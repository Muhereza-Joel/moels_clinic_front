export default function BuilderHeader({
    template,
    activeTab,
    onTabChange,
    onBack,
}) {
    return (
        <div className="flex items-center justify-between mb-6 border-b pb-4">
            {/* Left */}
            <div className="flex items-center gap-4">
                <button
                    onClick={onBack}
                    className="text-sm text-gray-600 hover:text-black"
                >
                    ← Back
                </button>

                <h1 className="text-xl font-semibold">
                    {template?.name ?? "Template Builder"}
                </h1>
            </div>

            {/* Tabs */}
            <div className="flex gap-2">
                {["editor", "mock", "preview"].map((tab) => (
                    <button
                        key={tab}
                        onClick={() => onTabChange(tab)}
                        className={`px-3 py-1 text-sm rounded-md capitalize
                            ${
                                activeTab === tab
                                    ? "bg-black text-white"
                                    : "bg-gray-100 hover:bg-gray-200"
                            }`}
                    >
                        {tab}
                    </button>
                ))}
            </div>
        </div>
    );
}
