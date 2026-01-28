import { useRef, useState } from "react";
import { useDrop } from "react-dnd";
import { useAppDispatch } from "@/hooks/useAppDispatch";
import {
    updateSection,
    removeSection,
    addBlock,
    updateBlock,
    removeBlock,
    moveBlock,
    syncSectionsToTemplate,
} from "@/features/section/sectionSlice";

import BlockEditor from "./BlockEditor";

export default function SectionEditor({ section, index, onAddBlock }) {
    const dispatch = useAppDispatch();
    const [isExpanded, setIsExpanded] = useState(true);
    const ref = useRef(null);

    const [{ isOver }, drop] = useDrop(
        () => ({
            accept: "BLOCK",
            drop: (item) => {
                dispatch(addBlock({ sectionIndex: index, type: item.type }));
                dispatch(syncSectionsToTemplate());
            },
            collect: (monitor) => ({
                isOver: monitor.isOver(),
            }),
        }),
        [index, dispatch],
    );

    drop(ref);

    const handleUpdateSection = (updates) => {
        dispatch(updateSection({ index, data: updates }));
        dispatch(syncSectionsToTemplate());
    };

    const handleUpdateGrid = (updates) => {
        handleUpdateSection({
            grid: {
                ...section.grid,
                ...updates,
            },
        });
    };

    const handleUpdateBlock = (blockIndex, updatedBlock) => {
        const items = [...section.grid.items];
        items[blockIndex] = updatedBlock;
        handleUpdateGrid({ items });
    };

    const handleRemoveBlock = (blockIndex) => {
        handleUpdateGrid({
            items: section.grid.items.filter((_, i) => i !== blockIndex),
        });
    };

    const handleMoveBlock = (from, to) => {
        const items = [...section.grid.items];
        const [moved] = items.splice(from, 1);
        items.splice(to, 0, moved);
        handleUpdateGrid({ items });
    };

    return (
        <div
            ref={ref}
            className="border rounded-xl bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 shadow-sm"
        >
            {/* Header - same as before but with updated handlers */}
            <div
                className="flex justify-between items-center p-4 bg-gray-50 dark:bg-gray-700/50 cursor-pointer rounded-t-xl"
                onClick={() => setIsExpanded(!isExpanded)}
            >
                <div className="flex items-center gap-3">
                    <div className="w-8 h-8 flex items-center justify-center bg-gray-200 dark:bg-gray-600 rounded-lg">
                        <span className="font-medium text-gray-700 dark:text-gray-300">
                            {index + 1}
                        </span>
                    </div>
                    <div>
                        <h3 className="font-semibold text-gray-900 dark:text-white">
                            {section.title || `Section ${index + 1}`}
                        </h3>
                        <p className="text-xs text-gray-500 dark:text-gray-400">
                            {section.grid.items.length} blocks •{" "}
                            {section.grid.columns} columns
                        </p>
                    </div>
                </div>
                <div className="flex items-center gap-2">
                    <button
                        type="button"
                        onClick={(e) => {
                            e.stopPropagation();
                            setIsExpanded(!isExpanded);
                        }}
                        className="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200 text-sm"
                    >
                        {isExpanded ? "Collapse" : "Expand"}
                    </button>
                    <button
                        type="button"
                        onClick={(e) => {
                            e.stopPropagation();
                            dispatch(removeSection(index));
                            dispatch(syncSectionsToTemplate());
                        }}
                        className="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 text-sm font-medium px-3 py-1 rounded hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                    >
                        Remove Section
                    </button>
                </div>
            </div>

            {isExpanded && (
                <div className="p-4 space-y-6">
                    <div className="space-y-3">
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Section Title
                        </label>
                        <input
                            type="text"
                            value={section.title || ""}
                            onChange={(e) =>
                                handleUpdateSection({ title: e.target.value })
                            }
                            className="w-full border rounded-lg p-3 bg-white dark:bg-gray-700 text-gray-900 dark:text-white border-gray-300 dark:border-gray-600"
                            placeholder="Enter section title"
                        />
                    </div>

                    <div className="space-y-3">
                        <div className="flex justify-between items-center">
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Grid Layout
                            </label>
                            <span className="text-sm font-medium text-gray-900 dark:text-white">
                                {section.grid.columns} columns
                            </span>
                        </div>
                        <input
                            type="range"
                            min="1"
                            max="4"
                            value={section.grid.columns}
                            onChange={(e) =>
                                handleUpdateGrid({
                                    columns: Number(e.target.value),
                                })
                            }
                            className="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-lg"
                        />
                    </div>

                    <div
                        className={`p-4 border-2 border-dashed rounded-lg transition-all duration-200 ${
                            isOver
                                ? "border-blue-500 dark:border-blue-400 bg-blue-50 dark:bg-blue-900/20"
                                : "border-gray-300 dark:border-gray-600"
                        }`}
                    >
                        <p className="text-center text-sm text-gray-600 dark:text-gray-400">
                            Drag blocks here or click "Add Block"
                        </p>
                    </div>

                    {section.grid.items.length > 0 ? (
                        <div className="space-y-4">
                            <h4 className="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Blocks ({section.grid.items.length})
                            </h4>
                            <div className="space-y-4">
                                {section.grid.items.map((block, i) => (
                                    <BlockEditor
                                        key={i}
                                        block={block}
                                        index={i}
                                        onUpdate={(updatedBlock) =>
                                            handleUpdateBlock(i, updatedBlock)
                                        }
                                        onRemove={() => handleRemoveBlock(i)}
                                        onMove={handleMoveBlock}
                                        sectionIndex={index}
                                    />
                                ))}
                            </div>
                        </div>
                    ) : (
                        <div className="text-center py-8 text-gray-500 dark:text-gray-400">
                            <p className="text-sm">
                                No blocks in this section yet.
                            </p>
                        </div>
                    )}

                    <div className="flex justify-center">
                        <button
                            type="button"
                            onClick={() => {
                                const type = prompt(
                                    "Enter block type (text, table, conditional, qrcode, chart):",
                                    "text",
                                );
                                if (
                                    type &&
                                    [
                                        "text",
                                        "table",
                                        "conditional",
                                        "qrcode",
                                        "chart",
                                    ].includes(type)
                                ) {
                                    const newBlock = { type };
                                    switch (type) {
                                        case "text":
                                            newBlock.content =
                                                "New text content";
                                            break;
                                        case "conditional":
                                            newBlock.condition = "{{variable}}";
                                            newBlock.block = {
                                                type: "text",
                                                content: "Conditional content",
                                            };
                                            break;
                                        case "table":
                                            newBlock.columns = [
                                                "Column 1",
                                                "Column 2",
                                            ];
                                            newBlock.rows = [
                                                ["Value 1", "Value 2"],
                                            ];
                                            break;
                                        case "qrcode":
                                            newBlock.value =
                                                "https://example.com";
                                            break;
                                        case "chart":
                                            newBlock.data = {
                                                type: "bar",
                                                values: [10, 20, 30, 40, 50],
                                            };
                                            break;
                                    }
                                    handleUpdateGrid({
                                        items: [
                                            ...section.grid.items,
                                            newBlock,
                                        ],
                                    });
                                }
                            }}
                            className="px-4 py-2 bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white rounded-lg font-medium"
                        >
                            + Add Block
                        </button>
                    </div>
                </div>
            )}
        </div>
    );
}
