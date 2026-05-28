import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

const TIPO_ICONS = {
    inicio_proceso:      { bg: 'bg-blue-100',   text: 'text-blue-600',   label: 'Proceso' },
    calificacion_final:  { bg: 'bg-green-100',  text: 'text-green-600',  label: 'Calificación' },
    apelacion_aprobada:  { bg: 'bg-purple-100', text: 'text-purple-600', label: 'Apelación' },
    apelacion_rechazada: { bg: 'bg-red-100',    text: 'text-red-600',    label: 'Apelación' },
    plazo_licencia:      { bg: 'bg-amber-100',  text: 'text-amber-600',  label: 'Licencia' },
};

export default function NotificacionesIndex({ notificaciones }) {
    const noLeidas = notificaciones.filter(n => !n.leida);

    return (
        <>
            <Head title="Notificaciones" />
            <AppLayout title="Notificaciones">

                {notificaciones.length === 0 ? (
                    <div className="bg-white rounded-xl border border-dashed border-gray-300 p-12 text-center">
                        <div className="text-4xl mb-3">🔔</div>
                        <p className="text-gray-400 text-sm">No tienes notificaciones.</p>
                    </div>
                ) : (
                    <>
                        {noLeidas.length > 0 && (
                            <p className="text-xs text-gray-500 mb-3">
                                {noLeidas.length} notificación{noLeidas.length > 1 ? 'es' : ''} nueva{noLeidas.length > 1 ? 's' : ''}
                            </p>
                        )}

                        <div className="space-y-2">
                            {notificaciones.map(n => {
                                const tipo = TIPO_ICONS[n.tipo] ?? { bg: 'bg-gray-100', text: 'text-gray-500', label: 'Sistema' };
                                return (
                                    <div
                                        key={n.id}
                                        className={`bg-white rounded-xl border px-5 py-4 flex gap-4 transition-colors ${
                                            !n.leida ? 'border-[#0096D6]/40 shadow-sm' : 'border-gray-200'
                                        }`}
                                    >
                                        {/* Indicador tipo */}
                                        <div className={`shrink-0 mt-0.5 w-8 h-8 rounded-full flex items-center justify-center ${tipo.bg}`}>
                                            <BellSmallIcon className={tipo.text} />
                                        </div>

                                        <div className="flex-1 min-w-0">
                                            <div className="flex items-start justify-between gap-2">
                                                <p className={`text-sm font-semibold ${!n.leida ? 'text-gray-900' : 'text-gray-700'}`}>
                                                    {n.titulo}
                                                    {!n.leida && (
                                                        <span className="ml-2 inline-block w-2 h-2 rounded-full bg-[#0096D6] align-middle" />
                                                    )}
                                                </p>
                                                <span className="text-xs text-gray-400 shrink-0">{n.created_at}</span>
                                            </div>
                                            <p className="text-sm text-gray-600 mt-1 leading-relaxed">{n.mensaje}</p>
                                            {n.url && (
                                                <Link
                                                    href={n.url}
                                                    className="mt-1.5 inline-block text-xs text-[#0096D6] hover:underline font-medium"
                                                >
                                                    Ver detalle →
                                                </Link>
                                            )}
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    </>
                )}
            </AppLayout>
        </>
    );
}

function BellSmallIcon({ className }) {
    return (
        <svg className={`w-4 h-4 ${className}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.8}
                d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
        </svg>
    );
}
