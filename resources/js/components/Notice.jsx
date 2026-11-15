export function Notice({ children, error = false }) {
    if (!children) return null;
    return <div role={error ? 'alert' : 'status'} className={`mb-5 rounded-2xl border px-4 py-3 text-sm ${error ? 'border-red-200 bg-red-50 text-red-800' : 'border-emerald-200 bg-emerald-50 text-emerald-900'}`}>{children}</div>;
}

export function PageTitle({ eyebrow, children, description }) {
    return <div className="mb-8">
        {eyebrow && <p className="mb-2 text-xs font-bold uppercase tracking-[0.18em] text-violet-800">{eyebrow}</p>}
        <h1 className="font-display text-3xl font-bold text-stone-950 sm:text-4xl">{children}</h1>
        {description && <p className="mt-3 max-w-2xl leading-7 text-stone-600">{description}</p>}
    </div>;
}
