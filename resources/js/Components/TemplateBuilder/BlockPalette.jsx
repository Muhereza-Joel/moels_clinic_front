import DraggableBlock from "./DraggableBlock";
import { BLOCK_TYPES } from "@/store/section/blockTypes";

export default function BlockPalette({ onAddBlock }) {
    return (
        <div className="space-y-2">
            {BLOCK_TYPES.map((b) => (
                <DraggableBlock key={b.type} {...b} onAdd={onAddBlock} />
            ))}
        </div>
    );
}
