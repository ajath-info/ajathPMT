import React, { useState, useEffect } from 'react';
import { Sparkles, HelpCircle, CheckCircle2, ChevronDown, ChevronUp } from 'lucide-react';

export interface HillScope {
  id: string;
  name: string;
  color: string;
  progress: number; // 0 to 100
  tasksCount?: number;
  completedCount?: number;
}

interface HillChartProps {
  scopes?: HillScope[];
  onUpdateScopePosition?: (scopeId: string, newPosition: number) => void;
  readOnly?: boolean;
}

// Default Basecamp scopes mapped to project functional tracks
const DEFAULT_SCOPES: HillScope[] = [
  { id: 'scope-1', name: 'UI & Visual Design', color: '#6366f1', progress: 85, tasksCount: 12, completedCount: 10 },
  { id: 'scope-2', name: 'Authentication & RLS', color: '#10b981', progress: 95, tasksCount: 8, completedCount: 8 },
  { id: 'scope-3', name: 'Realtime Campfire Chat', color: '#f59e0b', progress: 65, tasksCount: 6, completedCount: 4 },
  { id: 'scope-4', name: 'Task Dependency Engine', color: '#8b5cf6', progress: 40, tasksCount: 9, completedCount: 4 },
  { id: 'scope-5', name: 'Automated Check-ins', color: '#ec4899', progress: 25, tasksCount: 5, completedCount: 1 },
];

export function HillChart({ scopes = DEFAULT_SCOPES, onUpdateScopePosition, readOnly = false }: HillChartProps) {
  const [activeScopes, setActiveScopes] = useState<HillScope[]>(scopes.length > 0 ? scopes : DEFAULT_SCOPES);
  const [draggingId, setDraggingId] = useState<string | null>(null);
  const [hoveredScope, setHoveredScope] = useState<HillScope | null>(null);
  const [isExpanded, setIsExpanded] = useState(true);

  // Sync external scopes when loaded asynchronously
  useEffect(() => {
    if (!draggingId && scopes && scopes.length > 0) {
      setActiveScopes(scopes);
    }
  }, [scopes, draggingId]);

  // SVG Geometry Constants
  const width = 760;
  const height = 180;
  const baseLineY = 150;
  const peakY = 32;
  const marginX = 40;
  const usableWidth = width - marginX * 2;

  // Calculate coordinates (x, y) along the Basecamp bell curve
  // normalizedProgress is 0 to 100
  const getCoordinates = (normalizedProgress: number) => {
    const t = Math.max(0, Math.min(100, normalizedProgress)) / 100;
    const x = marginX + t * usableWidth;
    // Bell curve equation using Gaussian function
    const peakHeight = baseLineY - peakY;
    const stdDev = 0.22;
    const exponent = -Math.pow(t - 0.5, 2) / (2 * Math.pow(stdDev, 2));
    const y = baseLineY - peakHeight * Math.exp(exponent);
    return { x, y };
  };

  // Generate SVG curve path
  const generateCurvePath = () => {
    const points: string[] = [];
    const steps = 60;
    for (let i = 0; i <= steps; i++) {
      const pct = (i / steps) * 100;
      const { x, y } = getCoordinates(pct);
      points.push(`${i === 0 ? 'M' : 'L'} ${x.toFixed(1)} ${y.toFixed(1)}`);
    }
    return points.join(' ');
  };

  const curvePath = generateCurvePath();
  const fillPath = `${curvePath} L ${width - marginX} ${baseLineY} L ${marginX} ${baseLineY} Z`;

  // Drag interaction handlers
  const handleMouseDown = (id: string) => {
    if (readOnly) return;
    setDraggingId(id);
  };

  const handleMouseMove = (e: React.MouseEvent<SVGSVGElement>) => {
    if (!draggingId || readOnly) return;
    const rect = e.currentTarget.getBoundingClientRect();
    const mouseX = e.clientX - rect.left;
    const scale = width / rect.width;
    const svgX = mouseX * scale;
    const newProgress = Math.round(Math.max(0, Math.min(100, ((svgX - marginX) / usableWidth) * 100)));

    setActiveScopes((prev) =>
      prev.map((s) => (s.id === draggingId ? { ...s, progress: newProgress } : s))
    );

    if (onUpdateScopePosition) {
      onUpdateScopePosition(draggingId, newProgress);
    }
  };

  const handleMouseUp = () => {
    if (draggingId && onUpdateScopePosition) {
      const current = activeScopes.find((s) => s.id === draggingId);
      if (current) {
        onUpdateScopePosition(draggingId, current.progress);
      }
    }
    setDraggingId(null);
  };

  return (
    <div className="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/90 dark:border-slate-800 shadow-sm p-6 space-y-4">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-2.5">
          <div className="w-8 h-8 rounded-xl bg-amber-500/10 dark:bg-amber-400/10 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold">
            ⛰️
          </div>
          <div>
            <h3 className="font-extrabold text-base text-slate-900 dark:text-slate-100 flex items-center gap-2">
              Ajath PMT Hill Chart
              <span className="text-[11px] font-semibold text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-full">
                Visual Progress
              </span>
            </h3>
            <p className="text-xs text-slate-500 dark:text-slate-400">
              Track work from discovery to execution. No more guesswork on status.
            </p>
          </div>
        </div>

        <button
          onClick={() => setIsExpanded(!isExpanded)}
          className="p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
          title={isExpanded ? 'Collapse Hill Chart' : 'Expand Hill Chart'}
        >
          {isExpanded ? <ChevronUp className="w-4 h-4" /> : <ChevronDown className="w-4 h-4" />}
        </button>
      </div>

      {isExpanded && (
        <div className="space-y-4">
          {/* Visual SVG Hill Chart */}
          <div className="relative w-full overflow-x-auto select-none py-2">
            <svg
              viewBox={`0 0 ${width} ${height}`}
              className="w-full max-w-3xl mx-auto h-auto cursor-default"
              onMouseMove={handleMouseMove}
              onMouseUp={handleMouseUp}
              onMouseLeave={handleMouseUp}
            >
              <defs>
                <linearGradient id="hillFillGradient" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="0%" stopColor="#6366f1" stopOpacity="0.12" />
                  <stop offset="100%" stopColor="#6366f1" stopOpacity="0.01" />
                </linearGradient>
                <linearGradient id="curveStrokeGradient" x1="0" y1="0" x2="1" y2="0">
                  <stop offset="0%" stopColor="#94a3b8" />
                  <stop offset="50%" stopColor="#6366f1" />
                  <stop offset="100%" stopColor="#10b981" />
                </linearGradient>
              </defs>

              {/* Shaded Area Under Curve */}
              <path d={fillPath} fill="url(#hillFillGradient)" />

              {/* Baseline */}
              <line
                x1={marginX}
                y1={baseLineY}
                x2={width - marginX}
                y2={baseLineY}
                stroke="#cbd5e1"
                strokeWidth="1.5"
                strokeDasharray="4 4"
                className="dark:stroke-slate-700"
              />

              {/* Center Crest Divider Line */}
              <line
                x1={width / 2}
                y1={peakY - 10}
                x2={width / 2}
                y2={baseLineY}
                stroke="#94a3b8"
                strokeWidth="1.5"
                strokeDasharray="3 3"
                className="dark:stroke-slate-600 opacity-60"
              />

              {/* Main Hill Curve Line */}
              <path
                d={curvePath}
                fill="none"
                stroke="url(#curveStrokeGradient)"
                strokeWidth="3.5"
                strokeLinecap="round"
              />

              {/* Left Side Label: Figuring things out */}
              <text
                x={marginX + 40}
                y={baseLineY - 12}
                fontSize="11"
                fontWeight="700"
                fill="#64748b"
                className="tracking-wider uppercase"
              >
                ← FIGURING THINGS OUT
              </text>
              <text
                x={marginX + 40}
                y={baseLineY + 16}
                fontSize="10"
                fill="#94a3b8"
              >
                Unknowns, discovery, problem solving
              </text>

              {/* Right Side Label: Making it happen */}
              <text
                x={width - marginX - 190}
                y={baseLineY - 12}
                fontSize="11"
                fontWeight="700"
                fill="#10b981"
                className="tracking-wider uppercase text-right"
              >
                MAKING IT HAPPEN →
              </text>
              <text
                x={width - marginX - 190}
                y={baseLineY + 16}
                fontSize="10"
                fill="#94a3b8"
              >
                Execution, no mysteries, shipping
              </text>

              {/* Interactive Scope Dots on the Curve */}
              {activeScopes.map((scope) => {
                const { x, y } = getCoordinates(scope.progress);
                const isHovered = hoveredScope?.id === scope.id;
                const isDragging = draggingId === scope.id;

                return (
                  <g
                    key={scope.id}
                    className="cursor-pointer transition-transform duration-100"
                    onMouseEnter={() => setHoveredScope(scope)}
                    onMouseLeave={() => setHoveredScope(null)}
                    onMouseDown={() => handleMouseDown(scope.id)}
                  >
                    {/* Pulsing ring on hover/drag */}
                    {(isHovered || isDragging) && (
                      <circle
                        cx={x}
                        cy={y}
                        r="14"
                        fill={scope.color}
                        fillOpacity="0.2"
                        className="animate-pulse"
                      />
                    )}

                    {/* Dot Outer Border */}
                    <circle
                      cx={x}
                      cy={y}
                      r={isHovered || isDragging ? '8.5' : '7'}
                      fill="#ffffff"
                      stroke={scope.color}
                      strokeWidth="3.5"
                      className="shadow-md"
                    />

                    {/* Scope Label Pin */}
                    <text
                      x={x}
                      y={y - 14}
                      fontSize="10"
                      fontWeight="700"
                      fill={isHovered ? scope.color : '#334155'}
                      textAnchor="middle"
                      className="pointer-events-none drop-shadow-xs dark:fill-slate-200"
                    >
                      {scope.name}
                    </text>
                  </g>
                );
              })}
            </svg>
          </div>

          {/* Interactive Legend / Track List */}
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5 pt-2 border-t border-slate-100 dark:border-slate-800">
            {activeScopes.map((scope) => {
              const isUphill = scope.progress < 50;
              return (
                <div
                  key={scope.id}
                  onMouseEnter={() => setHoveredScope(scope)}
                  onMouseLeave={() => setHoveredScope(null)}
                  className="flex items-center justify-between p-2.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-800 hover:border-brand-500/50 transition-all text-xs"
                >
                  <div className="flex items-center gap-2 min-w-0">
                    <span
                      className="w-3 h-3 rounded-full shrink-0 shadow-xs"
                      style={{ backgroundColor: scope.color }}
                    />
                    <span className="font-bold text-slate-800 dark:text-slate-200 truncate">
                      {scope.name}
                    </span>
                  </div>

                  <div className="flex items-center gap-2 shrink-0">
                    <span
                      className={`text-[10px] font-bold px-2 py-0.5 rounded-full ${
                        isUphill
                          ? 'bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400'
                          : 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400'
                      }`}
                    >
                      {isUphill ? 'Uphill' : 'Downhill'} ({scope.progress}%)
                    </span>
                  </div>
                </div>
              );
            })}
          </div>
        </div>
      )}
    </div>
  );
}
