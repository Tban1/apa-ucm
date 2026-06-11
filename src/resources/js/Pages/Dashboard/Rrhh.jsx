import { Head } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

export default function RrhhDashboard({ periodo }) {
    return (
        <>
            <Head title="RRHH — Gestión de Personas" />
            <AppLayout title="Dirección de Gestión de Personas">
                <div className="space-y-6">
                    {periodo && (
                        <p className="text-sm text-gray-500">
                            Período activo: <span className="font-medium text-gray-800">{periodo.nombre}</span>
                        </p>
                    )}

                    <div className="bg-blue-50 border border-blue-200 rounded-xl p-6 text-center">
                        <p className="text-sm font-medium text-blue-800">
                            Panel de Dirección de Gestión de Personas
                        </p>
                        <p className="text-xs text-blue-600 mt-1">
                            Módulo en desarrollo — próximamente disponible.
                        </p>
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
