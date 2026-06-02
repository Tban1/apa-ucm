import { Head, Link, useForm, usePage, router } from '@inertiajs/react';
import { useState, useMemo, useRef } from 'react';
import AppLayout from '@/Layouts/AppLayout';

function formatDate(dateStr) {
    if (!dateStr) return '—';
    const [y, m, d] = dateStr.split('T')[0].split('-');
    return `${d}/${m}/${y}`;
}

// ── Modal agregar académico individual ────────────────────────────────────────
function ModalAgregarAcademico({ periodo, facultades, onClose }) {
    const { data, setData, post, processing, errors } = useForm({
        rut: '', nombre: '', facultad_id: '', categoria: '',
    });

    function submit(e) {
        e.preventDefault();
        post(route('analista.periodos.nominas.agregar', periodo.id), { onSuccess: onClose });
    }

    return (
        <div className="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
            <div className="bg-white rounded-xl shadow-lg w-full max-w-md p-6 space-y-4">
                <div className="flex items-center justify-between">
                    <h3 className="text-sm font-semibold text-gray-800">Agregar académico</h3>
                    <button onClick={onClose} className="text-gray-400 hover:text-gray-600 text-lg leading-none">×</button>
                </div>
                <p className="text-xs text-gray-500">Si el RUT ya existe en el sistema, se usará el usuario existente.</p>
                <form onSubmit={submit} className="space-y-3">
                    {[
                        { label: 'RUT', key: 'rut', placeholder: 'Ej: 12.345.678-9' },
                        { label: 'Nombre completo', key: 'nombre', placeholder: 'Ej: Juan Pérez Muñoz' },
                    ].map(({ label, key, placeholder }) => (
                        <div key={key}>
                            <label className="block text-xs font-medium text-gray-700 mb-1">{label}</label>
                            <input
                                type="text"
                                value={data[key]}
                                onChange={e => setData(key, e.target.value)}
                                placeholder={placeholder}
                                className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1B2D6B]/30"
                            />
                            {errors[key] && <p className="mt-1 text-xs text-red-600">{errors[key]}</p>}
                        </div>
                    ))}

                    <div>
                        <label className="block text-xs font-medium text-gray-700 mb-1">Facultad</label>
                        <select
                            value={data.facultad_id}
                            onChange={e => setData('facultad_id', e.target.value)}
                            className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1B2D6B]/30"
                        >
                            <option value="">— Seleccionar —</option>
                            {facultades.map(f => <option key={f.id} value={f.id}>{f.nombre}</option>)}
                        </select>
                        {errors.facultad_id && <p className="mt-1 text-xs text-red-600">{errors.facultad_id}</p>}
                    </div>

                    <div>
                        <label className="block text-xs font-medium text-gray-700 mb-1">Categoría académica</label>
                        <select
                            value={data.categoria}
                            onChange={e => setData('categoria', e.target.value)}
                            className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1B2D6B]/30"
                        >
                            <option value="">— Seleccionar —</option>
                            {['auxiliar', 'adjunto', 'titular', 'jerarquizado'].map(c => (
                                <option key={c} value={c}>{c.charAt(0).toUpperCase() + c.slice(1)}</option>
                            ))}
                        </select>
                        {errors.categoria && <p className="mt-1 text-xs text-red-600">{errors.categoria}</p>}
                    </div>

                    <div className="flex gap-3 justify-end pt-2">
                        <button type="button" onClick={onClose} className="text-sm text-gray-500 hover:text-gray-700">
                            Cancelar
                        </button>
                        <button type="submit" disabled={processing}
                            className="bg-[#1B2D6B] text-white text-sm px-4 py-2 rounded-lg hover:bg-[#152558] disabled:opacity-60">
                            {processing ? 'Agregando...' : 'Agregar'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}

// ── Panel importación Excel con mapeo de columnas ─────────────────────────────
const CAMPOS_REQUERIDOS = [
    { key: 'col_rut',    label: 'RUT',       requerido: true },
    { key: 'col_nombre', label: 'Nombre',    requerido: true },
];
const CAMPOS_OPCIONALES = [
    { key: 'col_facultad',    label: 'Facultad' },
    { key: 'col_categoria',   label: 'Categoría académica' },
    { key: 'col_horas_isem',  label: 'Horas contrato I Sem' },
    { key: 'col_horas_iisem', label: 'Horas contrato II Sem' },
];

function PanelExcel({ periodo }) {
    const { flash } = usePage().props;
    const preview = flash?.excel_preview;

    const fileRef = useRef();
    const [uploading, setUploading] = useState(false);
    const [mapeo, setMapeo]         = useState({});
    const [tieneEncabezado, setTieneEncabezado] = useState(true);
    const [importando, setImportando] = useState(false);

    function subirArchivo(e) {
        e.preventDefault();
        if (!fileRef.current?.files[0]) return;
        setUploading(true);
        const fd = new FormData();
        fd.append('archivo', fileRef.current.files[0]);
        router.post(
            route('analista.periodos.nominas.preview-excel', periodo.id),
            fd,
            { forceFormData: true, onFinish: () => setUploading(false) }
        );
    }

    function importar() {
        if (!preview?.path) return;
        setImportando(true);
        router.post(
            route('analista.periodos.nominas.importar-excel', periodo.id),
            { path: preview.path, tiene_encabezado: tieneEncabezado, ...mapeo },
            { onFinish: () => setImportando(false) }
        );
    }

    const columnaOpts = preview?.columnas.map((col, i) => ({ value: i, label: `[${i + 1}] ${col || '(sin nombre)'}` })) ?? [];
    const mapeoCompleto = mapeo.col_rut !== undefined && mapeo.col_nombre !== undefined;

    return (
        <div className="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <p className="text-sm font-semibold text-gray-700">Importar desde Excel / CSV</p>
            <p className="text-xs text-gray-400">Formatos soportados: .xlsx, .xls, .csv — máx. 5 MB</p>

            {/* Paso 1: subir archivo */}
            <form onSubmit={subirArchivo} className="flex gap-2 items-center">
                <input ref={fileRef} type="file" accept=".xlsx,.xls,.csv"
                    className="text-xs text-gray-600 file:mr-2 file:text-xs file:border file:border-gray-300 file:rounded file:px-2 file:py-1 file:bg-gray-50 file:cursor-pointer"
                />
                <button type="submit" disabled={uploading}
                    className="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded-lg disabled:opacity-50 whitespace-nowrap">
                    {uploading ? 'Leyendo...' : 'Cargar archivo'}
                </button>
            </form>

            {/* Paso 2: mapeo de columnas */}
            {preview && (
                <div className="space-y-4 border-t border-gray-100 pt-4">
                    <div>
                        <p className="text-xs font-medium text-gray-600 mb-2">Vista previa del archivo</p>
                        <div className="overflow-x-auto rounded border border-gray-200">
                            <table className="text-xs w-full">
                                <thead className="bg-gray-50">
                                    <tr>
                                        {preview.columnas.map((c, i) => (
                                            <th key={i} className="px-3 py-1.5 text-left font-medium text-gray-600 whitespace-nowrap">
                                                [{i + 1}] {c || '—'}
                                            </th>
                                        ))}
                                    </tr>
                                </thead>
                                <tbody>
                                    {preview.preview_rows.map((row, ri) => (
                                        <tr key={ri} className="border-t border-gray-100">
                                            {row.map((cell, ci) => (
                                                <td key={ci} className="px-3 py-1 text-gray-600 max-w-[120px] truncate">{cell}</td>
                                            ))}
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div>
                        <label className="flex items-center gap-2 text-xs text-gray-600 cursor-pointer">
                            <input type="checkbox" checked={tieneEncabezado}
                                onChange={e => setTieneEncabezado(e.target.checked)}
                                className="rounded border-gray-300" />
                            La primera fila es encabezado (omitirla en la importación)
                        </label>
                    </div>

                    <div>
                        <p className="text-xs font-medium text-gray-600 mb-2">
                            Asignar columnas del archivo a campos del sistema
                        </p>
                        <div className="grid grid-cols-2 gap-3">
                            {[...CAMPOS_REQUERIDOS, ...CAMPOS_OPCIONALES].map(campo => (
                                <div key={campo.key}>
                                    <label className="block text-xs text-gray-500 mb-1">
                                        {campo.label}
                                        {campo.requerido && <span className="text-red-500 ml-0.5">*</span>}
                                    </label>
                                    <select
                                        value={mapeo[campo.key] ?? ''}
                                        onChange={e => setMapeo(prev => ({
                                            ...prev,
                                            [campo.key]: e.target.value === '' ? undefined : Number(e.target.value),
                                        }))}
                                        className="w-full border border-gray-300 rounded px-2 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-[#1B2D6B]/30"
                                    >
                                        <option value="">{campo.requerido ? '— Seleccionar —' : '— No importar —'}</option>
                                        {columnaOpts.map(o => (
                                            <option key={o.value} value={o.value}>{o.label}</option>
                                        ))}
                                    </select>
                                </div>
                            ))}
                        </div>
                    </div>

                    <button
                        onClick={importar}
                        disabled={!mapeoCompleto || importando}
                        className="w-full bg-[#1B2D6B] text-white text-sm py-2 rounded-lg hover:bg-[#152558] disabled:opacity-50 transition-colors"
                    >
                        {importando ? 'Importando...' : 'Importar nómina'}
                    </button>
                    {!mapeoCompleto && (
                        <p className="text-xs text-red-500">Debes asignar al menos las columnas de RUT y Nombre.</p>
                    )}
                </div>
            )}
        </div>
    );
}

// ── Componente principal ──────────────────────────────────────────────────────
export default function NominaCreate({ periodo, facultades, academicos, nominasEnPeriodo }) {
    const { flash } = usePage().props;
    const [facultadId, setFacultadId]   = useState('');
    const [editingId, setEditingId]     = useState(null);
    const [obsInput, setObsInput]       = useState('');
    const [savingId, setSavingId]       = useState(null);
    const [showModal, setShowModal]     = useState(false);
    const [activeTab, setActiveTab]     = useState('manual'); // 'manual' | 'excel'

    const { data, setData, post, processing, errors } = useForm({ user_ids: [] });

    const nominaMap = useMemo(() => {
        const m = {};
        nominasEnPeriodo.forEach(n => { m[n.user_id] = n; });
        return m;
    }, [nominasEnPeriodo]);

    const yaEnSet = useMemo(
        () => new Set(nominasEnPeriodo.map(n => n.user_id)),
        [nominasEnPeriodo]
    );

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
        setData('user_ids', current.includes(userId)
            ? current.filter(id => id !== userId)
            : [...current, userId]
        );
    }
    function selectAll() { setData('user_ids', pendientes.map(a => a.id)); }
    function clearAll()  { setData('user_ids', []); }
    function onFacultadChange(id) { setFacultadId(id); setData('user_ids', []); setEditingId(null); }
    function submitNomina(e) { e.preventDefault(); post(`/analista/periodos/${periodo.id}/nominas`); }

    function abrirLicencia(nominaId)    { setEditingId(nominaId); setObsInput(''); }
    function confirmarLicencia(nominaId) {
        setSavingId(nominaId);
        router.patch(`/analista/nominas/${nominaId}/licencia`,
            { con_licencia: true, observacion_licencia: obsInput },
            { preserveScroll: true, onFinish: () => setSavingId(null), onSuccess: () => { setEditingId(null); setObsInput(''); } }
        );
    }
    function quitarLicencia(nominaId) {
        setSavingId(nominaId);
        router.patch(`/analista/nominas/${nominaId}/licencia`,
            { con_licencia: false, observacion_licencia: null },
            { preserveScroll: true, onFinish: () => setSavingId(null) }
        );
    }

    // URL de exportación con filtro de facultad opcional
    const exportUrl = facultadId
        ? route('analista.periodos.nominas.exportar', periodo.id) + `?facultad_id=${facultadId}`
        : route('analista.periodos.nominas.exportar', periodo.id);

    const totalNomina    = nominasEnPeriodo.length;
    const totalLicencias = nominasEnPeriodo.filter(n => n.con_licencia).length;

    return (
        <>
            <Head title={`Nómina — ${periodo.nombre}`} />
            <AppLayout title="Gestionar Nómina">

                {/* Breadcrumb */}
                <div className="flex items-center justify-between -mt-4 mb-6">
                    <div className="flex items-center gap-2 text-sm text-gray-500">
                        <Link href="/analista/periodos" className="hover:text-gray-700">Períodos</Link>
                        <span>/</span>
                        <span className="text-gray-700 font-medium">{periodo.nombre}</span>
                        <span>/</span>
                        <span>Nómina</span>
                    </div>

                    {/* Botones superiores */}
                    <div className="flex items-center gap-2">
                        <a href={exportUrl}
                            className="text-xs border border-gray-300 text-gray-600 hover:bg-gray-50 px-3 py-1.5 rounded-lg transition-colors">
                            ↓ Exportar Excel
                        </a>
                        <button
                            onClick={() => setShowModal(true)}
                            className="text-xs bg-[#1B2D6B] text-white px-3 py-1.5 rounded-lg hover:bg-[#152558] transition-colors">
                            + Agregar académico
                        </button>
                    </div>
                </div>

                {/* Mensajes flash */}
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

                    {/* Panel izquierdo */}
                    <div className="space-y-5">
                        {/* Info período */}
                        <div className="bg-white rounded-xl border border-gray-200 p-5">
                            <p className="text-xs text-gray-400 uppercase tracking-wide mb-1">Período</p>
                            <p className="font-semibold text-gray-900">{periodo.nombre}</p>
                            <p className="text-sm text-gray-500 mt-1">Año {periodo.anio}</p>
                            <div className="mt-3 pt-3 border-t border-gray-100 space-y-2">
                                <div className="flex items-center justify-between">
                                    <span className="text-sm text-gray-500">Total en nómina</span>
                                    <span className="text-lg font-bold text-[#1B2D6B]">{totalNomina}</span>
                                </div>
                                {totalLicencias > 0 && (
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm text-gray-500">Casos especiales</span>
                                        <span className="text-sm font-semibold text-amber-600">{totalLicencias}</span>
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* Selector de facultad */}
                        <div className="bg-white rounded-xl border border-gray-200 p-5">
                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                Filtrar por facultad
                            </label>
                            <select
                                value={facultadId}
                                onChange={e => onFacultadChange(e.target.value)}
                                className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1B2D6B]/30 focus:border-[#1B2D6B]"
                            >
                                <option value="">— Todas las facultades —</option>
                                {facultades.map(f => (
                                    <option key={f.id} value={f.id}>{f.nombre}</option>
                                ))}
                            </select>
                        </div>

                        {/* Panel Excel */}
                        <PanelExcel periodo={periodo} />
                    </div>

                    {/* Panel derecho */}
                    <div className="lg:col-span-2">
                        {!facultadId ? (
                            <div className="bg-white rounded-xl border border-dashed border-gray-300 p-10 text-center h-full flex items-center justify-center">
                                <p className="text-gray-400 text-sm">Selecciona una facultad para ver sus académicos.</p>
                            </div>
                        ) : (
                            <form onSubmit={submitNomina} className="space-y-4">

                                {/* Académicos disponibles */}
                                <div className="bg-white rounded-xl border border-gray-200">
                                    <div className="flex items-center justify-between px-5 py-3 border-b border-gray-100">
                                        <p className="text-sm font-semibold text-gray-700">
                                            Disponibles para agregar
                                            {pendientes.length > 0 && (
                                                <span className="ml-2 text-xs font-normal text-gray-400">({pendientes.length})</span>
                                            )}
                                        </p>
                                        {pendientes.length > 0 && (
                                            <div className="flex gap-3">
                                                <button type="button" onClick={selectAll}
                                                    className="text-xs text-[#0096D6] hover:underline">
                                                    Todos
                                                </button>
                                                {data.user_ids.length > 0 && (
                                                    <button type="button" onClick={clearAll}
                                                        className="text-xs text-gray-400 hover:underline">
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
                                                    <input type="checkbox"
                                                        checked={data.user_ids.includes(a.id)}
                                                        onChange={() => toggleAcademico(a.id)}
                                                        className="h-4 w-4 rounded border-gray-300 text-[#1B2D6B]"
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

                                {errors.user_ids && (
                                    <p className="text-xs text-red-600">{errors.user_ids}</p>
                                )}

                                {pendientes.length > 0 && (
                                    <div className="flex items-center gap-4">
                                        <button type="submit"
                                            disabled={processing || data.user_ids.length === 0}
                                            className="bg-[#1B2D6B] text-white text-sm font-medium px-5 py-2.5 rounded-lg hover:bg-[#152558] disabled:opacity-50">
                                            {processing
                                                ? 'Agregando...'
                                                : `Agregar${data.user_ids.length > 0 ? ` ${data.user_ids.length}` : ''} a la nómina`}
                                        </button>
                                        <Link href="/analista/periodos"
                                            className="text-sm text-gray-500 hover:text-gray-700">
                                            Volver
                                        </Link>
                                    </div>
                                )}

                                {/* Ya en la nómina + gestión de casos especiales */}
                                {enNomina.length > 0 && (
                                    <div className="bg-white rounded-xl border border-gray-200">
                                        <div className="px-5 py-3 border-b border-gray-100">
                                            <p className="text-sm font-semibold text-gray-700">
                                                Ya en la nómina
                                                <span className="ml-2 text-xs font-normal text-gray-400">({enNomina.length})</span>
                                            </p>
                                        </div>
                                        <ul className="divide-y divide-gray-100">
                                            {enNomina.map(a => {
                                                const nom       = nominaMap[a.id];
                                                const isEditing = editingId === nom?.id;
                                                const isSaving  = savingId === nom?.id;

                                                return (
                                                    <li key={a.id} className="px-5 py-3">
                                                        <div className="flex items-center gap-3">
                                                            <input type="checkbox" checked disabled
                                                                className="h-4 w-4 rounded border-gray-300 opacity-50" />
                                                            <div className="flex-1 min-w-0">
                                                                <p className="text-sm font-medium text-gray-800 truncate">{a.name}</p>
                                                                {a.rut && <p className="text-xs text-gray-400">{a.rut}</p>}
                                                            </div>
                                                            <div className="flex items-center gap-2 shrink-0">
                                                                {nom?.con_licencia ? (
                                                                    <>
                                                                        <span className="text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200 px-2 py-0.5 rounded-full">
                                                                            Caso especial
                                                                        </span>
                                                                        <button type="button" disabled={isSaving}
                                                                            onClick={() => quitarLicencia(nom.id)}
                                                                            className="text-xs text-gray-400 hover:text-red-500 disabled:opacity-40">
                                                                            {isSaving ? '...' : 'Quitar'}
                                                                        </button>
                                                                    </>
                                                                ) : (
                                                                    <button type="button"
                                                                        onClick={() => abrirLicencia(nom?.id)}
                                                                        className="text-xs text-amber-600 hover:text-amber-800 font-medium">
                                                                        + Caso especial
                                                                    </button>
                                                                )}
                                                            </div>
                                                        </div>

                                                        {nom?.con_licencia && nom?.observacion_licencia && !isEditing && (
                                                            <div className="mt-1.5 ml-7 text-xs text-gray-500 bg-amber-50 rounded-md px-3 py-1.5">
                                                                <span className="font-medium text-amber-700">Motivo:</span> {nom.observacion_licencia}
                                                                <span className="ml-2 text-gray-400">· {formatDate(nom.updated_at)}</span>
                                                            </div>
                                                        )}

                                                        {isEditing && (
                                                            <div className="mt-2 ml-7 space-y-2">
                                                                <input type="text" value={obsInput}
                                                                    onChange={e => setObsInput(e.target.value)}
                                                                    placeholder="Motivo del caso especial"
                                                                    className="w-full border border-amber-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-200"
                                                                    autoFocus
                                                                />
                                                                <div className="flex items-center gap-3">
                                                                    <button type="button"
                                                                        disabled={!obsInput.trim() || isSaving}
                                                                        onClick={() => confirmarLicencia(nom.id)}
                                                                        className="text-xs font-medium bg-amber-500 text-white px-3 py-1.5 rounded-lg hover:bg-amber-600 disabled:opacity-50">
                                                                        {isSaving ? 'Guardando...' : 'Confirmar'}
                                                                    </button>
                                                                    <button type="button" onClick={() => setEditingId(null)}
                                                                        className="text-xs text-gray-400 hover:text-gray-600">
                                                                        Cancelar
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        )}
                                                    </li>
                                                );
                                            })}
                                        </ul>
                                    </div>
                                )}
                            </form>
                        )}
                    </div>
                </div>
            </AppLayout>

            {showModal && (
                <ModalAgregarAcademico
                    periodo={periodo}
                    facultades={facultades}
                    onClose={() => setShowModal(false)}
                />
            )}
        </>
    );
}
