import { useEffect } from "react";
import { useAppSelector } from "@/hooks/useAppSelector";
import { useAppDispatch } from "@/hooks/useAppDispatch";
import {
    selectSections,
    addSection,
    addBlock,
    syncSectionsToTemplate,
    loadTemplateSections,
} from "@/features/section/sectionSlice";
import {
    selectSelectedTemplate,
    updateTemplate,
} from "@/features/template/templateSlice";

import SectionEditor from "../SectionEditor";
import BlockPalette from "../BlockPalette";

export default function EditorView({ templateUuid }) {
    const dispatch = useAppDispatch();
    const sections = useAppSelector(selectSections);
    const selectedTemplate = useAppSelector(selectSelectedTemplate);

    useEffect(() => {
        if (templateUuid) {
            dispatch(loadTemplateSections(templateUuid));
        }
    }, [templateUuid, dispatch]);

    const handleAddBlock = (sectionIndex, type) => {
        dispatch(addBlock({ sectionIndex, type }));
        dispatch(syncSectionsToTemplate());
    };

    const handleSave = () => {
        if (selectedTemplate) {
            dispatch(
                updateTemplate({
                    uuid: selectedTemplate.uuid,
                    // The data object sent to the Laravel API
                    data: {
                        name: selectedTemplate.name,
                        code: selectedTemplate.code,
                        version: selectedTemplate.version,
                        active: selectedTemplate.active,
                        mock_schema: selectedTemplate.mock_schema,
                        layout: {
                            ...selectedTemplate.layout, // Keep orientation, page_size, etc.
                            sections: sections, // Use current state of sections
                            footer: {
                                // Explicitly enforce footer
                                text: "Generated on {{now}}",
                            },
                        },
                    },
                }),
            );
        }
    };

    // Prevent "Cannot read properties of null" error
    if (!selectedTemplate || !selectedTemplate.layout) {
        return (
            <div className="flex items-center justify-center h-64">
                <div className="text-gray-500 animate-pulse text-lg">
                    Loading template editor...
                </div>
            </div>
        );
    }

    return (
        <div className="grid lg:grid-cols-4 gap-6">
            {/* Sidebar Palette */}
            {/* Sidebar Palette */}
            <aside className="lg:col-span-1">
                <div className="sticky top-4">
                    <h3 className="text-sm font-semibold mb-4 text-gray-500 uppercase tracking-wider">
                        Blocks
                    </h3>

                    <BlockPalette
                        onAddBlock={(type) => handleAddBlock(0, type)}
                    />
                </div>
            </aside>

            {/* Main Canvas Area */}
            <div className="lg:col-span-3 space-y-6">
                {selectedTemplate.layout.sections?.map((section, i) => (
                    <SectionEditor
                        key={section.id || i}
                        section={section}
                        index={i}
                        onAddBlock={(type) => handleAddBlock(i, type)}
                    />
                ))}

                {/* Optional: Visual footer preview */}
                <div className="p-4 bg-gray-50 dark:bg-gray-800/50 border border-t-2 border-gray-200 dark:border-gray-700 rounded-b-lg">
                    <span className="text-xs font-bold text-gray-400 uppercase">
                        Enforced Footer
                    </span>
                    <p className="text-sm text-gray-600 dark:text-gray-400 italic">
                        Generated on {"{{"}now{"}}"}
                    </p>
                </div>

                <div className="pt-4 space-y-4">
                    <button
                        type="button"
                        onClick={() => {
                            dispatch(addSection());
                            dispatch(syncSectionsToTemplate());
                        }}
                        className="w-full py-3 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg text-gray-600 dark:text-gray-400 hover:border-gray-400 dark:hover:border-gray-500 hover:text-gray-800 dark:hover:text-gray-300 transition-colors"
                    >
                        + Add Section
                    </button>

                    <button
                        type="button"
                        onClick={handleSave}
                        className="w-full py-3 bg-indigo-600 text-white font-bold rounded-lg hover:bg-indigo-700 shadow-md transition-all active:scale-95"
                    >
                        Save Template Changes
                    </button>
                </div>
            </div>
        </div>
    );
}
