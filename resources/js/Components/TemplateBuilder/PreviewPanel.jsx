export default function PreviewPanel({
    records,
    recordId,
    onSelect,
    previewUrl,
    onPreview,
}) {
    return (
        <>
            <select
                className="w-full border rounded p-2 mb-3"
                value={recordId || ""}
                onChange={(e) => onSelect(e.target.value)}
            >
                <option value="">-- Select record --</option>
                {records.map((r) => (
                    <option key={r.id} value={r.id}>
                        {r.patient?.name ?? `Record ${r.id}`}
                    </option>
                ))}
            </select>

            <button
                onClick={onPreview}
                disabled={!recordId}
                className="w-full bg-green-600 text-white rounded p-2"
            >
                Generate Preview
            </button>

            {previewUrl && (
                <a
                    href={previewUrl}
                    target="_blank"
                    className="block mt-3 text-center bg-blue-600 text-white rounded p-2"
                >
                    Open Preview
                </a>
            )}
        </>
    );
}
