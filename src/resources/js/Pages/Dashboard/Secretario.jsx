import { Head } from '@inertiajs/react';
import { usePage } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

export default function Secretario() {
    const { auth } = usePage().props;
    const facultad = auth.user.facultad?.nombre ?? '—';

    return (
        <>
            <Head title="Panel Secretario" />
            <AppLayout title="Panel Secretario">
                <p className="text-sm text-gray-500 -mt-4 mb-6">Facultad: {facultad}</p>
                <div className="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
                    <StatCard label="Expedientes abiertos" value="—" />
                    <StatCard label="Expedientes cerrados" value="—" />
                    <StatCard label="Apelaciones pendientes" value="—" />
                </div>
                <Placeholder text="Gestión de expedientes de la facultad — disponible en próximos sprints" />
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
