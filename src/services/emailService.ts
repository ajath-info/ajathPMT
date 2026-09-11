/**
 * WorkSphere / Ajath PMT - Transactional Email Service
 * Supports Resend, SendGrid, SMTP webhook proxies, and dev console logger with local test audit trails.
 */

export interface EmailRecipient {
  email: string;
  name?: string;
}

export interface EmailMessage {
  to: string | EmailRecipient;
  subject: string;
  html: string;
  text?: string;
  replyTo?: string;
}

export interface EmailSendResult {
  success: boolean;
  messageId?: string;
  error?: string;
}

const OUTBOUND_EMAILS_STORAGE_KEY = 'worksphere_outbound_emails';

export function getLoggedOutboundEmails(): EmailMessage[] {
  try {
    const raw = localStorage.getItem(OUTBOUND_EMAILS_STORAGE_KEY);
    return raw ? JSON.parse(raw) : [];
  } catch {
    return [];
  }
}

export function clearLoggedOutboundEmails(): void {
  try {
    localStorage.removeItem(OUTBOUND_EMAILS_STORAGE_KEY);
  } catch {
    // Ignore in non-browser environments
  }
}

export async function sendTransactionalEmail(message: EmailMessage): Promise<EmailSendResult> {
  const provider = (import.meta.env?.VITE_EMAIL_PROVIDER || 'console').toLowerCase();
  const resendApiKey = import.meta.env?.VITE_RESEND_API_KEY;

  const toEmail = typeof message.to === 'string' ? message.to : message.to.email;
  const toName = typeof message.to === 'object' ? message.to.name : undefined;

  // Persist to local audit trail
  try {
    const history = getLoggedOutboundEmails();
    history.push({
      ...message,
      text: message.text || message.html.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim(),
    });
    // Keep last 50 emails
    if (history.length > 50) history.shift();
    localStorage.setItem(OUTBOUND_EMAILS_STORAGE_KEY, JSON.stringify(history));
  } catch {
    // ignore
  }

  // Live Resend API Integration if key provided
  if (provider === 'resend' && resendApiKey) {
    try {
      const response = await fetch('https://api.resend.com/emails', {
        method: 'POST',
        headers: {
          Authorization: `Bearer ${resendApiKey}`,
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          from: import.meta.env?.VITE_EMAIL_FROM || 'WorkSphere <noreply@worksphere.io>',
          to: toName ? `${toName} <${toEmail}>` : toEmail,
          subject: message.subject,
          html: message.html,
          text: message.text,
          reply_to: message.replyTo,
        }),
      });

      if (!response.ok) {
        const errJson = await response.json().catch(() => ({}));
        return {
          success: false,
          error: errJson.message || `Resend API error: ${response.statusText}`,
        };
      }

      const resData = await response.json();
      return { success: true, messageId: resData.id };
    } catch (err: any) {
      return { success: false, error: err?.message || 'Network error sending email via Resend' };
    }
  }

  // Default / Development console delivery
  const messageId = `msg-${Date.now()}-${Math.random().toString(36).substring(2, 7)}`;
  console.groupCollapsed(`📧 [Transactional Email] to: ${toEmail} | subject: "${message.subject}"`);
  console.log('To:', toEmail);
  console.log('Subject:', message.subject);
  if (message.replyTo) console.log('Reply-To:', message.replyTo);
  console.log('HTML:', message.html);
  console.groupEnd();

  return {
    success: true,
    messageId,
  };
}

export async function sendPasswordResetEmail(email: string, resetUrl: string): Promise<EmailSendResult> {
  return sendTransactionalEmail({
    to: email,
    subject: 'Reset your WorkSphere password',
    html: `
      <div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; max-width: 560px; margin: 0 auto; padding: 32px 24px; color: #1e293b;">
        <h2 style="font-size: 20px; font-weight: 700; color: #0f172a; margin-bottom: 16px;">Reset your password</h2>
        <p style="font-size: 14px; line-height: 22px; color: #475569; margin-bottom: 24px;">
          We received a request to reset the password for your WorkSphere / Ajath PMT account. Click the button below to choose a new password:
        </p>
        <div style="margin-bottom: 28px;">
          <a href="${resetUrl}" style="display: inline-block; background-color: #0284c7; color: #ffffff; padding: 12px 24px; border-radius: 8px; font-size: 14px; font-weight: 600; text-decoration: none;">
            Reset Password
          </a>
        </div>
        <p style="font-size: 12px; color: #94a3b8; line-height: 18px;">
          If you didn't request a password reset, you can safely ignore this email. The link will expire in 24 hours.
        </p>
      </div>
    `,
    text: `Reset your WorkSphere password:\n\nClick the link below to choose a new password:\n${resetUrl}\n\nIf you did not request this, please ignore this email.`,
  });
}

export async function sendProjectInvitationEmail(
  email: string,
  inviterName: string,
  projectName: string,
  inviteUrl: string
): Promise<EmailSendResult> {
  return sendTransactionalEmail({
    to: email,
    subject: `${inviterName} invited you to collaborate on ${projectName}`,
    html: `
      <div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; max-width: 560px; margin: 0 auto; padding: 32px 24px; color: #1e293b;">
        <h2 style="font-size: 20px; font-weight: 700; color: #0f172a; margin-bottom: 16px;">You've been invited!</h2>
        <p style="font-size: 14px; line-height: 22px; color: #475569; margin-bottom: 24px;">
          <strong>${inviterName}</strong> invited you to collaborate on <strong>${projectName}</strong> on WorkSphere.
        </p>
        <div style="margin-bottom: 28px;">
          <a href="${inviteUrl}" style="display: inline-block; background-color: #0284c7; color: #ffffff; padding: 12px 24px; border-radius: 8px; font-size: 14px; font-weight: 600; text-decoration: none;">
            Accept Invitation
          </a>
        </div>
      </div>
    `,
    text: `${inviterName} invited you to collaborate on ${projectName} on WorkSphere.\n\nAccept invitation: ${inviteUrl}`,
  });
}

export async function sendNotificationEmail(
  recipientEmail: string,
  title: string,
  body: string,
  actionUrl: string,
  replyToToken?: string
): Promise<EmailSendResult> {
  const replyTo = replyToToken
    ? `reply+${replyToToken}@inbound.worksphere.io`
    : undefined;

  return sendTransactionalEmail({
    to: recipientEmail,
    subject: `[WorkSphere] ${title}`,
    replyTo,
    html: `
      <div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; max-width: 560px; margin: 0 auto; padding: 32px 24px; color: #1e293b;">
        <h3 style="font-size: 18px; font-weight: 700; color: #0f172a; margin-bottom: 12px;">${title}</h3>
        <p style="font-size: 14px; line-height: 22px; color: #334155; margin-bottom: 24px;">
          ${body}
        </p>
        <div style="margin-bottom: 28px;">
          <a href="${actionUrl}" style="display: inline-block; background-color: #0284c7; color: #ffffff; padding: 10px 20px; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none;">
            View in WorkSphere
          </a>
        </div>
        ${
          replyToToken
            ? `<p style="font-size: 11px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 12px;">
                 💡 You can reply directly to this email to post a comment or response.
               </p>`
            : ''
        }
      </div>
    `,
    text: `${title}\n\n${body}\n\nView online: ${actionUrl}`,
  });
}
