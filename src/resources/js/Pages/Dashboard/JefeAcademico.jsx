import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

export default function JefeAcademico({ stats, periodo }) {
    return (
        <>
            <Head title="Panel Jefe Académico" />
            <AppLayout title="Panel Jefe Académico">
                {periodo && (
                    <p className="text-sm text-gray-500 -mt-4 mb-6">
                        Período: <span className="font-medium text-gray-700">{periodo.nombre} {periodo.anio}</span>
                    </p>
                )}

                <div className="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-8">
                    <StatCard label="Calificaciones pendientes" value={stats.pendientes} color="amber" />
                    <StatCard label="Calificaciones emitidas"   value={stats.emitidas}   color="green" />
                </div>

                <div className="bg-white rounded-xl border border-gray-200 p-6 flex items-center justify-between">
                    <div>
                        <p className="font-semibold text-gray-800">Académicos del departamento</p>
                        <p className="text-sm text-gray-500 mt-0.5">Revise y califique los expedientes evaluados por la CCA.</p>
                    </div>
                    <Link
                        href="/jefe/academicos"
                        className="bg-[#1B2D6B] hover:bg-[#152558] text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors"
                    >
                        Ver académicos
                    </Link>
                </div>
            </AppLayout>
        </>
    );
}

function StatCard({ label, value, color }) {
    const colors = {
        amber: 'text-amber-600',
        green: 'text-green-600',
    };
    return (
        <div className="bg-white rounded-xl border border-gray-200 p-5">
            <p className="text-sm text-gray-500">{label}</p>
            <p className={`text-3xl font-bold mt-1 ${colors[color] ?? 'text-gray-900'}`}>{value}</p>
        </div>
    );
}
