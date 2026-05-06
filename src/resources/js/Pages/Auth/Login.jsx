import { useForm, Head } from '@inertiajs/react';

export default function Login() {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    function submit(e) {
        e.preventDefault();
        post('/login');
    }

    return (
        <>
            <Head title="Iniciar sesión — Sistema APA UCM" />

            <div className="min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-[#1B2D6B] via-[#0F1E4A] to-[#0a1535] relative overflow-hidden">

                {/* Círculos decorativos de fondo */}
                <div className="absolute -top-20 -left-20 w-80 h-80 rounded-full bg-[#0096D6] opacity-10 pointer-events-none" />
                <div className="absolute -bottom-24 -right-24 w-96 h-96 rounded-full bg-[#F5A623] opacity-5 pointer-events-none" />
                <div className="absolute top-1/2 -right-12 w-56 h-56 rounded-full bg-[#0096D6] opacity-[0.07] pointer-events-none" />

                <div className="w-full max-w-md relative z-10">

                    {/* Título sobre la card */}
                    <div className="text-center mb-6">
                        <h1 className="text-white text-xl font-bold leading-snug tracking-tight">
                            Sistema de Gestión de<br />Calificaciones Académicas
                        </h1>
                    </div>

                    {/* Card del formulario */}
                    <div className="bg-white rounded-2xl shadow-2xl px-8 py-8">

                        {/* Logo dentro de la card */}
                        <div className="flex flex-col items-center mb-6">
                            <img
                                src="/img/image.png"
                                alt="Universidad Católica del Maule"
                                className="h-14 w-auto mb-3"
                            />
                            <p className="text-sm text-gray-400">
                                Ingresa con tu cuenta institucional
                            </p>
                        </div>

                        <form onSubmit={submit} className="space-y-5">

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1.5">
                                    Correo institucional
                                </label>
                                <input
                                    type="email"
                                    value={data.email}
                                    onChange={e => setData('email', e.target.value)}
                                    className="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#0096D6] focus:border-transparent transition"
                                    placeholder="usuario@ucm.cl"
                                    autoComplete="email"
                                    autoFocus
                                    required
                                />
                                {errors.email && (
                                    <p className="text-red-500 text-xs mt-1.5">{errors.email}</p>
                                )}
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1.5">
                                    Contraseña
                                </label>
                                <input
                                    type="password"
                                    value={data.password}
                                    onChange={e => setData('password', e.target.value)}
                                    className="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#0096D6] focus:border-transparent transition"
                                    autoComplete="current-password"
                                    required
                                />
                                {errors.password && (
                                    <p className="text-red-500 text-xs mt-1.5">{errors.password}</p>
                                )}
                            </div>

                            <div className="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    id="remember"
                                    checked={data.remember}
                                    onChange={e => setData('remember', e.target.checked)}
                                    className="w-4 h-4 rounded border-gray-300 accent-[#1B2D6B] cursor-pointer"
                                />
                                <label htmlFor="remember" className="text-sm text-gray-600 select-none cursor-pointer">
                                    Recordarme
                                </label>
                            </div>

                            <button
                                type="submit"
                                disabled={processing}
                                className="w-full bg-[#1B2D6B] hover:bg-[#0D1B45] active:bg-[#0a1535] text-white font-medium py-2.5 px-4 rounded-lg text-sm transition-colors disabled:opacity-60 disabled:cursor-not-allowed mt-1"
                            >
                                {processing ? 'Ingresando...' : 'Ingresar'}
                            </button>

                        </form>
                    </div>

                    <p className="text-center text-blue-200 text-xs mt-6 opacity-40">
                        © {new Date().getFullYear()} Universidad Católica del Maule · Vicerrectoría Académica
                    </p>

                </div>
            </div>
        </>
    );
}
