import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { useState, useMemo } from 'react';
import AppLayout from '@/Layouts/AppLayout';

export default function NominaCreate({ periodo, facultades, academicos, yaEnNomina }) {
    const { flash } = usePage().props;
    const [facultadId, setFacultadId] = useState('');

    const { data, setData, post, processing, errors } = useForm({ user_ids: [] });

    const yaEnSet = useMemo(() => new Set(yaEnNomina), [yaEnNomina]);

    const academicosDeFactultad = useMemo(
        () => academicos.filter(a => a.facultad_id === facultadId),
        [academicos, facultadId]
    );

    const pendientes = useMemo(
        () => academicosDeFactultad.filter(a => !yaEnSet.has(a.id)),
        [academicosDeFactultad, yaEnSet]
    );

    const enNomina = useMemo(
        () => academicosDeFactultad.filter(a => yaEnSet.has(a.id)),
        [academicosDeFactultad, yaEnSet]
    );

    function toggleAcademico(userId) {
        const current = data.user_ids;
        setData('user_ids',
            current.includes(userId)
                ? current.filter(id => id !== userId)
                : [...current, userId]
        );
    }

    function selectAll() {
        const disponibles = pendientes.map(a => a.id);
        setData('user_ids', disponibles);
    }

    function clearAll() {
        setData('user_ids', []);
    }

    function onFacultadChange(id) {
        setFacultadId(id);
        setData('user_ids', []);
    }

    function submit(e) {
        e.preventDefault();
        post(`/analista/periodos/${periodo.id}/nominas`);
    }

    const totalNomina = yaEnNomina.length;

    return (
        <>
            <Head title={`Nómina — ${periodo.nombre}`} />
            <AppLayout title="Gestionar Nómina">

                {/* Breadcrumb */}
                <div className="flex items-center gap-2 text-sm text-gray-500 -mt-4 mb-6">
                    <Link href="/analista/periodos" className="hover:text-gray-700">Períodos</Link>
                    <span>/</span>
                    <span className="text-gray-700 font-medium">{periodo.nombre}</span>
                    <span>/</span>
                    <span>Nómina</span>
                </div>

                {flash?.success && (
                    <div className="mb-5 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                        {flash.success}
                    </div>
                )}
                {flash?.error && (
                    <div className="mb-5 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                        {flash.error}
                    </div>
                )}

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    {/* Panel izquierdo: info del período + selector */}
                    <div className="space-y-5">
                        <div className="bg-white rounded-xl border border-gray-200 p-5">
                            <p className="text-xs text-gray-400 uppercase tracking-wide mb-1">Período</p>
                            <p className="font-semibold text-gray-900">{periodo.nombre}</p>
                            <p className="text-sm text-gray-500 mt-1">Año {periodo.anio}</p>
                            <div className="mt-3 pt-3 border-t border-gray-100 flex items-center justify-between">
                                <span className="text-sm text-gray-500">Total en nómina</span>
                                <span className="text-lg font-bold text-[#1B2D6B]">{totalNomina}</span>
                            </div>
                        </div>

                        <div className="bg-white rounded-xl border border-gray-200 p-5">
                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                Seleccionar facultad
                            </label>
                            <select
                                value={facultadId}
                                onChange={e => onFacultadChange(e.target.value)}
                                className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1B2D6B]/30 focus:border-[#1B2D6B]"
                            >
                                <option value="">— Selecciona una facultad —</option>
                                {facultades.map(f => (
                                    <option key={f.id} value={f.id}>{f.nombre}</option>
                                ))}
                            </select>
                        </div>
                    </div>

                    {/* Panel derecho: lista de académicos */}
                    <div className="lg:col-span-2">
                        {!facultadId ? (
                            <div className="bg-white rounded-xl border border-dashed border-gray-300 p-10 text-center h-full flex items-center justify-center">
                                <p className="text-gray-400 text-sm">Selecciona una facultad para ver sus académicos.</p>
                            </div>
                        ) : (
                            <form onSubmit={submit} className="space-y-4">

                                {/* Académicos disponibles para agregar */}
                                <div className="bg-white rounded-xl border border-gray-200">
                                    <div className="flex items-center justify-between px-5 py-3 border-b border-gray-100">
                                        <p className="text-sm font-semibold text-gray-700">
                                            Académicos disponibles
                                            {pendientes.length > 0 && (
                                                <span className="ml-2 text-xs font-normal text-gray-400">
                                                    ({pendientes.length})
                                                </span>
                                            )}
                                        </p>
                                        {pendientes.length > 0 && (
                                            <div className="flex gap-3">
                                                <button type="button" onClick={selectAll}
                                                    className="text-xs text-[#0096D6] hover:underline">
                                                    Seleccionar todos
                                                </button>
                                                {data.user_ids.length > 0 && (
                                                    <button type="button" onClick={clearAll}
                                                        className="text-xs text-gray-400 hover:text-gray-600 hover:underline">
                                                        Limpiar
                                                    </button>
                                                )}
                                            </div>
                                        )}
                                    </div>

                                    {pendientes.length === 0 ? (
                                        <p className="px-5 py-6 text-sm text-gray-400 text-center">
                                            Todos los académicos de esta facultad ya están en la nómina.
                                        </p>
                                    ) : (
                                        <ul className="divide-y divide-gray-50">
                                            {pendientes.map(a => (
                                                <li key={a.id}
                                                    className="flex items-center gap-3 px-5 py-3 hover:bg-gray-50 cursor-pointer"
                                                    onClick={() => toggleAcademico(a.id)}
                                                >
                                                    <input
                                                        type="checkbox"
                                                        checked={data.user_ids.includes(a.id)}
                                                        onChange={() => toggleAcademico(a.id)}
                                                        className="h-4 w-4 rounded border-gray-300 text-[#1B2D6B] focus:ring-[#1B2D6B]/30"
                                                        onClick={e => e.stopPropagation()}
                                                    />
                                                    <div className="flex-1">
                                                        <p className="text-sm font-medium text-gray-800">{a.name}</p>
                                                        {a.rut && <p className="text-xs text-gray-400">{a.rut}</p>}
                                                    </div>
                                                </li>
                                            ))}
                                        </ul>
                                    )}
                                </div>

                                {/* Académicos ya en la nómina (esta facultad) */}
                                {enNomina.length > 0 && (
                                    <div className="bg-white rounded-xl border border-gray-200">
                                        <div className="px-5 py-3 border-b border-gray-100">
                                            <p className="text-sm font-semibold text-gray-700">
                                                Ya en la nómina
                                                <span className="ml-2 text-xs font-normal text-gray-400">
                                                    ({enNomina.length})
                                                </span>
                                            </p>
                                        </div>
                                        <ul className="divide-y divide-gray-50">
                                            {enNomina.map(a => (
                                                <li key={a.id} className="flex items-center gap-3 px-5 py-3 opacity-60">
                                                    <input type="checkbox" checked disabled
                                                        className="h-4 w-4 rounded border-gray-300" />
                                                    <div className="flex-1">
                                                        <p className="text-sm text-gray-700">{a.name}</p>
                                                        {a.rut && <p className="text-xs text-gray-400">{a.rut}</p>}
                                                    </div>
                                                    <span className="text-xs text-green-600 font-medium">Agregado</span>
                                                </li>
                                            ))}
                                        </ul>
                                    </div>
                                )}

                                {errors.user_ids && (
                                    <p className="text-xs text-red-600">{errors.user_ids}</p>
                                )}

                                {pendientes.length > 0 && (
                                    <div className="flex items-center gap-4">
                                        <button
                                            type="submit"
                                            disabled={processing || data.user_ids.length === 0}
                                            className="bg-[#1B2D6B] text-white text-sm font-medium px-5 py-2.5 rounded-lg hover:bg-[#152558] disabled:opacity-50 transition-colors"
                                        >
                                            {processing
                                                ? 'Agregando...'
                                                : `Agregar ${data.user_ids.length > 0 ? data.user_ids.length : ''} a la nómina`.trim()
                                            }
                                        </button>
                                        <Link href="/analista/periodos"
                                            className="text-sm text-gray-500 hover:text-gray-700">
                                            Volver a períodos
                                        </Link>
                                    </div>
                                )}
                            </form>
                        )}
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
