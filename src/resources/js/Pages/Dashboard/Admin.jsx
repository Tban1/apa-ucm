import { Head } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

export default function Admin() {
    return (
        <>
            <Head title="Panel Administrador" />
            <AppLayout title="Panel de Administración">
                <div className="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
                    <StatCard label="Facultades" value="—" />
                    <StatCard label="Usuarios activos" value="—" />
                    <StatCard label="Períodos activos" value="—" />
                </div>
                <Placeholder text="Gestión de usuarios y configuración del sistema — disponible en próximos sprints" />
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
