import { publishScheduledDiscussions } from './discussionService';
import { purgeExpiredTrashedProjects, getProjects } from './projectService';
import { getTasks } from './taskService';
import { getCalendarEvents } from './calendarService';
import { createNotification } from './notificationService';

let workerIntervalId: any = null;
const notifiedTaskReminders = new Set<string>();
const notifiedEventReminders = new Set<string>();

/**
 * Executes a single tick of the autonomous Basecamp 4 background worker.
 * 1. Publishes scheduled discussions whose scheduled time has passed.
 * 2. Purges soft-trashed projects past their 30-day retention window.
 * 3. Dispatches reminder notifications for upcoming/overdue tasks & events.
 */
export async function tickBackgroundWorker(): Promise<{
  publishedDiscussions: number;
  purgedProjects: number;
  taskReminders: number;
  eventReminders: number;
}> {
  let publishedDiscussions = 0;
  let purgedProjects = 0;
  let taskReminders = 0;
  let eventReminders = 0;

  const now = new Date();

  // 1. Publish scheduled discussions
  try {
    publishedDiscussions = await publishScheduledDiscussions();
  } catch (err) {
    console.warn('[BackgroundWorker] Error publishing scheduled discussions:', err);
  }

  // 2. Purge expired trashed projects (30-day retention rule)
  try {
    purgedProjects = await purgeExpiredTrashedProjects(30);
  } catch (err) {
    console.warn('[BackgroundWorker] Error purging expired trashed projects:', err);
  }

  // 3. Due Task & Event Reminders across active projects
  try {
    const activeProjects = await getProjects();
    const targetProjects = activeProjects.slice(0, 10); // Check active projects

    for (const proj of targetProjects) {
      // A. Tasks due within the next 24 hours
      try {
        const tasks = await getTasks(proj.id);
        for (const t of tasks) {
          if (!t.due_date || t.status === 'COMPLETED') continue;

          const dueDate = new Date(t.due_date);
          const diffHours = (dueDate.getTime() - now.getTime()) / (1000 * 60 * 60);

          // If due within next 24 hours (or up to 24h overdue) and not yet notified
          if (diffHours >= -24 && diffHours <= 24) {
            const reminderKey = `${t.id}-${dueDate.toISOString().slice(0, 10)}`;
            if (!notifiedTaskReminders.has(reminderKey)) {
              notifiedTaskReminders.add(reminderKey);

              const assignees = t.assignees || [];
              const isOverdue = diffHours < 0;
              const title = isOverdue
                ? `Task Overdue: ${t.title}`
                : `Task Due Soon: ${t.title}`;
              const message = isOverdue
                ? `Task in "${proj.name}" is overdue (${dueDate.toLocaleDateString()}).`
                : `Task in "${proj.name}" is due within 24 hours.`;

              for (const a of assignees) {
                const targetUserId = (a as any).id || (a as any).user_id;
                if (targetUserId) {
                  await createNotification({
                    user_id: targetUserId,
                    type: isOverdue ? 'TASK_OVERDUE' : 'TASK_DUE_SOON',
                    title,
                    message,
                    link_url: `/projects/${proj.id}/todos`,
                    entity_type: 'TASK',
                    entity_id: t.id,
                  });
                  taskReminders++;
                }
              }
            }
          }
        }
      } catch (err) {
        // Individual project task check failed non-critically
      }

      // B. Calendar events starting within the next 2 hours
      try {
        const events = await getCalendarEvents(proj.id);
        for (const evt of events) {
          if (!evt.start_at) continue;
          const startTime = new Date(evt.start_at);
          const diffMinutes = (startTime.getTime() - now.getTime()) / (1000 * 60);

          if (diffMinutes >= 0 && diffMinutes <= 120) {
            const eventKey = `${evt.id}-${startTime.toISOString().slice(0, 13)}`;
            if (!notifiedEventReminders.has(eventKey)) {
              notifiedEventReminders.add(eventKey);

              // Notify creator / attendees
              if (evt.created_by) {
                await createNotification({
                  user_id: evt.created_by,
                  type: 'EVENT_STARTING_SOON',
                  title: `Event starting soon: ${evt.title}`,
                  message: `Scheduled for ${startTime.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })} in "${proj.name}".`,
                  link_url: `/projects/${proj.id}/calendar`,
                  entity_type: 'EVENT',
                  entity_id: evt.id,
                });
                eventReminders++;
              }
            }
          }
        }
      } catch (err) {
        // Individual project calendar check failed non-critically
      }
    }
  } catch (err) {
    console.warn('[BackgroundWorker] Error during due task/event check:', err);
  }

  return {
    publishedDiscussions,
    purgedProjects,
    taskReminders,
    eventReminders,
  };
}

/**
 * Starts the autonomous background worker with the specified interval.
 * Defaults to running every 60 seconds (1 minute).
 */
export function startBackgroundWorker(intervalMs: number = 60_000): () => void {
  if (workerIntervalId) {
    clearInterval(workerIntervalId);
  }

  // Execute initial tick immediately
  tickBackgroundWorker().catch((err) =>
    console.warn('[BackgroundWorker] Initial tick error:', err)
  );

  // Set recurring tick interval
  workerIntervalId = setInterval(() => {
    tickBackgroundWorker().catch((err) =>
      console.warn('[BackgroundWorker] Interval tick error:', err)
    );
  }, intervalMs);

  return stopBackgroundWorker;
}

/**
 * Stops the autonomous background worker.
 */
export function stopBackgroundWorker(): void {
  if (workerIntervalId) {
    clearInterval(workerIntervalId);
    workerIntervalId = null;
  }
}
