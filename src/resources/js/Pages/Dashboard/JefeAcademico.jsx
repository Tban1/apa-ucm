import { Head } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

export default function JefeAcademico() {
    return (
        <>
            <Head title="Panel Jefe Académico" />
            <AppLayout title="Panel Jefe Académico">
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-8">
                    <StatCard label="Calificaciones pendientes" value="—" />
                    <StatCard label="Calificaciones emitidas" value="—" />
                </div>
                <Placeholder text="Emisión de calificaciones de jefatura — disponible en próximos sprints" />
            </AppLayout>
        </>
    );
}

function StatCard({ label, value }) {
    return (
        <div className="bg-white rounded-xl border border-gray-200 p-5">
            <p className="text-sm text-gray-500">{label}</p>
            <p className="text-2xl font-bold text-gray-900 mt-1">{value}</p>
        </div>
    );
}

function Placeholder({ text }) {
    return (
        <div className="bg-white rounded-xl border border-dashed border-gray-300 p-10 text-center">
            <p className="text-gray-400 text-sm">{text}</p>
        </div>
    );
}
