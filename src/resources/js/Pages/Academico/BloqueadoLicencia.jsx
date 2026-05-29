import { Head, router } from '@inertiajs/react';

export default function BloqueadoLicencia() {
    return (
        <>
            <Head title="Acceso suspendido" />
            <div className="min-h-full flex items-center justify-center p-6 bg-gray-50">
                <div className="max-w-md w-full bg-white rounded-xl border border-amber-200 shadow-sm p-8 text-center">
                    <div className="text-4xl mb-4">🏥</div>
                    <h1 className="text-lg font-bold text-gray-900 mb-2">Acceso suspendido</h1>
                    <p className="text-sm text-gray-600 leading-relaxed">
                        Su acceso al sistema está suspendido por una <strong>licencia médica activa</strong>.
                        Cuando el Analista CCDA registre su reincorporación, podrá volver a ingresar.
                    </p>
                    <button type="button" onClick={() => router.post('/logout')}
                        className="mt-6 text-sm text-gray-500 hover:text-gray-700 underline">
                        Cerrar sesión
                    </button>
                </div>
            </div>
        </>
    );
}
