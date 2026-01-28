import { useAppSelector } from "@/hooks/useAppSelector";
import { selectTemplates } from "@/features/template/templateSlice";

export default function TemplateListView({ onSelect, onCreate }) {
    const templates = useAppSelector(selectTemplates);

    return (
        <div className="p-6">
            <button
                onClick={onCreate}
                className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium mb-6"
            >
                + Create New Template
            </button>

            <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                {templates.map((t) => (
                    <div
                        key={t.uuid}
                        onClick={() => onSelect(t)}
                        className="border border-gray-200 dark:border-gray-700 rounded-lg p-4 cursor-pointer hover:shadow-md dark:hover:shadow-gray-800 transition-shadow bg-white dark:bg-gray-800"
                    >
                        <h3 className="font-semibold text-gray-900 dark:text-white mb-2">
                            {t.name}
                        </h3>
                        <p className="text-sm text-gray-600 dark:text-gray-400">
                            {t.code}
                        </p>
                    </div>
                ))}
            </div>

            {templates.length === 0 && (
                <div className="text-center py-12 text-gray-500 dark:text-gray-400">
                    <p className="mb-2">No templates found.</p>
                    <p>Click "Create New Template" to get started.</p>
                </div>
            )}
        </div>
    );
}
