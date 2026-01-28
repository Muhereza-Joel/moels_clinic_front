import { useRef } from "react";
import { useDrag, useDrop } from "react-dnd";
import { useAppDispatch } from "@/hooks/useAppDispatch";
import {
    updateBlock,
    removeBlock,
    moveBlock,
    syncSectionsToTemplate,
} from "@/features/section/sectionSlice";

export default function BlockEditor({ block, index, sectionIndex }) {
    const dispatch = useAppDispatch();
    const ref = useRef(null);

    if (!block || !block.type) {
        return (
            <div className="p-4 border rounded bg-gray-100 text-gray-600">
                Invalid block
            </div>
        );
    }

    // Drag & Drop
    const [{ isDragging }, drag] = useDrag(
        () => ({
            type: "BLOCK_ITEM",
            item: { index, sectionIndex },
            collect: (monitor) => ({
                isDragging: monitor.isDragging(),
            }),
        }),
        [index, sectionIndex],
    );

    const [, drop] = useDrop(
        () => ({
            accept: "BLOCK_ITEM",
            hover: (draggedItem) => {
                if (!ref.current) return;

                const dragIndex = draggedItem.index;
                const hoverIndex = index;
                const dragSection = draggedItem.sectionIndex;
                const hoverSection = sectionIndex;

                if (dragIndex === hoverIndex && dragSection === hoverSection)
                    return;

                if (dragSection === hoverSection) {
                    dispatch(
                        moveBlock({ sectionIndex, dragIndex, hoverIndex }),
                    );
                    dispatch(syncSectionsToTemplate());
                    draggedItem.index = hoverIndex;
                }
            },
        }),
        [index, sectionIndex],
    );

    drag(drop(ref));

    const updateField = (field, value) => {
        const updatedBlock = { ...block, [field]: value };
        dispatch(
            updateBlock({
                sectionIndex,
                blockIndex: index,
                block: updatedBlock,
            }),
        );
        dispatch(syncSectionsToTemplate());
    };

    const handleRemove = () => {
        dispatch(removeBlock({ sectionIndex, blockIndex: index }));
        dispatch(syncSectionsToTemplate());
    };

    const updateNestedField = (nestedPath, value) => {
        const updatedBlock = { ...block };
        const keys = nestedPath.split(".");
        let current = updatedBlock;

        for (let i = 0; i < keys.length - 1; i++) {
            if (!current[keys[i]]) {
                current[keys[i]] = {};
            }
            current = current[keys[i]];
        }

        current[keys[keys.length - 1]] = value;
        dispatch(
            updateBlock({
                sectionIndex,
                blockIndex: index,
                block: updatedBlock,
            }),
        );
        dispatch(syncSectionsToTemplate());
    };

    // Safe renderEditor implementation
    const renderEditor = () => {
        switch (block.type) {
            case "text":
                return (
                    <input
                        type="text"
                        value={block.content || ""}
                        onChange={(e) => updateField("content", e.target.value)}
                        className="w-full border rounded px-2 py-1"
                    />
                );
            case "qrcode":
                return (
                    <input
                        type="text"
                        value={block.value || ""}
                        onChange={(e) => updateField("value", e.target.value)}
                        placeholder="QR Code value"
                        className="w-full border rounded px-2 py-1"
                    />
                );
            case "chart":
                return (
                    <div>
                        <select
                            value={block.data?.type || "bar"}
                            onChange={(e) =>
                                updateNestedField("data.type", e.target.value)
                            }
                            className="border rounded px-2 py-1 mb-2"
                        >
                            <option value="bar">Bar</option>
                            <option value="line">Line</option>
                            <option value="pie">Pie</option>
                        </select>
                        <textarea
                            value={block.data?.values?.join(", ") || ""}
                            onChange={(e) =>
                                updateNestedField(
                                    "data.values",
                                    e.target.value.split(",").map(Number),
                                )
                            }
                            className="w-full border rounded px-2 py-1"
                        />
                    </div>
                );
            case "table":
                return (
                    <div>
                        <p className="text-sm text-gray-600">Table editor</p>
                        {/* You can expand with inputs for rows/columns */}
                    </div>
                );
            case "conditional":
                return (
                    <div>
                        <input
                            type="text"
                            value={block.condition || ""}
                            onChange={(e) =>
                                updateField("condition", e.target.value)
                            }
                            placeholder="Condition"
                            className="w-full border rounded px-2 py-1 mb-2"
                        />
                        <BlockEditor
                            block={block.block}
                            index={0}
                            sectionIndex={sectionIndex}
                        />
                    </div>
                );
            default:
                return (
                    <div className="text-gray-500">
                        Unknown block type: {block.type}
                    </div>
                );
        }
    };

    return (
        <div
            ref={ref}
            className={`border rounded-lg p-4 bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 transition-all duration-200 ${
                isDragging
                    ? "opacity-50 scale-95"
                    : "hover:shadow-md dark:hover:shadow-gray-800"
            }`}
        >
            <div className="flex justify-between items-center mb-3">
                <div className="flex items-center gap-2">
                    <div
                        className="w-6 h-6 flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded cursor-move"
                        onClick={(e) => e.stopPropagation()}
                    >
                        <span className="text-xs select-none">⋮⋮</span>
                    </div>
                    <div className="font-medium text-gray-900 dark:text-white capitalize">
                        {block.type} Block
                    </div>
                </div>
                <div className="flex items-center gap-2">
                    <button
                        onClick={handleRemove}
                        className="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 text-sm font-medium px-2 py-1 rounded hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                        type="button"
                    >
                        Remove
                    </button>
                </div>
            </div>
            <div className="mt-3" onClick={(e) => e.stopPropagation()}>
                {renderEditor()}
            </div>
        </div>
    );
}
