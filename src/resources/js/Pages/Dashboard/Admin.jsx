import { Head, Link } from '@inertiajs/react';
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

                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <ActionCard
                        title="Configuración de Semestres"
                        description="Define las fechas de cierre del I y II Semestre para la declaración APA."
                        href="/admin/configuracion-semestres"
                        linkLabel="Configurar semestres"
                        primary
                    />
                    <ActionCard
                        title="Gestión de usuarios"
                        description="Administra los usuarios del sistema y sus roles."
                        href="#"
                        linkLabel="Próximamente"
                    />
                </div>
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

function ActionCard({ title, description, href, linkLabel, primary = false }) {
    const cls = `self-start text-sm font-medium px-4 py-2 rounded-lg transition-colors ${
        primary
            ? 'bg-[#1B2D6B] text-white hover:bg-[#152558]'
            : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
    }`;

    return (
        <div className="bg-white rounded-xl border border-gray-200 p-5 flex flex-col gap-3">
            <div>
                <p className="font-semibold text-gray-900 text-sm">{title}</p>
                <p className="text-sm text-gray-500 mt-1">{description}</p>
            </div>
            {href === '#' ? (
                <span className="self-start text-sm font-medium px-4 py-2 rounded-lg bg-gray-100 text-gray-400 cursor-not-allowed">
                    {linkLabel}
                </span>
            ) : (
                <Link href={href} className={cls}>{linkLabel}</Link>
            )}
        </div>
    );
}
