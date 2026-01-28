import { useDrag } from "react-dnd";

export default function DraggableBlock({ type, label, icon, onAdd }) {
    const [{ isDragging }, drag] = useDrag(() => ({
        type: "BLOCK",
        item: { type },
        collect: (monitor) => ({
            isDragging: monitor.isDragging(),
        }),
    }));

    return (
        <div
            ref={drag}
            onClick={() => onAdd(type)}
            className={`flex items-center gap-2 p-3 border rounded cursor-move
        ${isDragging ? "opacity-50" : ""}`}
        >
            <span>{icon}</span>
            <span className="text-sm font-medium">{label}</span>
        </div>
    );
}
