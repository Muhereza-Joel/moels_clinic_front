export default function FooterEditor({ footer, onChange }) {
    return (
        <div className="mt-6 border-t pt-4">
            <label className="block text-sm font-medium mb-1">Footer</label>
            <input
                className="w-full border rounded p-2"
                value={footer.text || ""}
                onChange={(e) => onChange(e.target.value)}
                placeholder="Generated on {{now}}"
            />
        </div>
    );
}
