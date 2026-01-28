import { useAppSelector } from "@/hooks/useAppSelector";
import { useAppDispatch } from "@/hooks/useAppDispatch";
import {
    selectMockData,
    setModelName,
    addField,
    updateField,
    removeField,
    addNestedModel,
    updateNestedModel,
    updateNestedField,
    generateMockData,
    resetMock,
} from "@/features/mock/mockSlice";
import { generatePreview } from "@/features/preview/previewSlice";
import { selectSelectedTemplate } from "@/features/template/templateSlice";

export default function MockView() {
    const dispatch = useAppDispatch();
    const mockData = useAppSelector(selectMockData);
    const selectedTemplate = useAppSelector(selectSelectedTemplate);

    const handlePreviewWithMock = () => {
        const mockDataObj = generateMockData(mockData);
        dispatch(
            generatePreview({
                templateUuid: selectedTemplate.uuid,
                useMock: true,
                mockData: mockDataObj,
            }),
        );
    };

    const handleSaveMock = () => {
        const mockDataObj = generateMockData(mockData);
        // Save to localStorage or backend
        localStorage.setItem("templateMockData", JSON.stringify(mockDataObj));
        alert("Mock data saved!");
    };

    return (
        <div className="border rounded-lg p-4 bg-gray-50 dark:bg-gray-800">
            <h3 className="text-lg font-semibold mb-4 text-gray-900 dark:text-white">
                Model Mock Builder: {mockData.modelName}
            </h3>

            <div className="mb-4">
                <label className="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">
                    Model Name
                </label>
                <input
                    type="text"
                    value={mockData.modelName}
                    onChange={(e) => dispatch(setModelName(e.target.value))}
                    className="w-full border rounded p-2 bg-white dark:bg-gray-700"
                    placeholder="e.g., Patient, Invoice, Visit"
                />
            </div>

            <div className="mb-6">
                <div className="flex justify-between items-center mb-3">
                    <h4 className="font-medium text-gray-900 dark:text-white">
                        Fields
                    </h4>
                    <button
                        onClick={() => dispatch(addField())}
                        className="px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700"
                    >
                        + Add Field
                    </button>
                </div>

                <div className="space-y-3">
                    {mockData.fields.map((field, index) => (
                        <div key={index} className="flex gap-2 items-center">
                            <input
                                type="text"
                                value={field.name}
                                onChange={(e) =>
                                    dispatch(
                                        updateField({
                                            index,
                                            key: "name",
                                            value: e.target.value,
                                        }),
                                    )
                                }
                                className="flex-1 border rounded p-2 text-sm"
                                placeholder="Field name"
                            />
                            <select
                                value={field.type}
                                onChange={(e) =>
                                    dispatch(
                                        updateField({
                                            index,
                                            key: "type",
                                            value: e.target.value,
                                        }),
                                    )
                                }
                                className="w-32 border rounded p-2 text-sm"
                            >
                                <option value="string">String</option>
                                <option value="number">Number</option>
                                <option value="boolean">Boolean</option>
                                <option value="date">Date</option>
                                <option value="array">Array</option>
                            </select>
                            <input
                                type="text"
                                value={field.value}
                                onChange={(e) =>
                                    dispatch(
                                        updateField({
                                            index,
                                            key: "value",
                                            value: e.target.value,
                                        }),
                                    )
                                }
                                className="flex-1 border rounded p-2 text-sm"
                                placeholder="Mock value"
                            />
                            <button
                                onClick={() => dispatch(removeField(index))}
                                className="px-2 py-1 text-red-600 hover:text-red-800"
                            >
                                ✕
                            </button>
                        </div>
                    ))}
                </div>
            </div>

            <div className="mb-6">
                <div className="flex justify-between items-center mb-3">
                    <h4 className="font-medium text-gray-900 dark:text-white">
                        Nested Objects/Arrays
                    </h4>
                    <button
                        onClick={() => dispatch(addNestedModel())}
                        className="px-3 py-1 text-sm bg-green-600 text-white rounded hover:bg-green-700"
                    >
                        + Add Nested
                    </button>
                </div>

                <div className="space-y-4">
                    {mockData.nestedModels.map((model, modelIndex) => (
                        <div
                            key={modelIndex}
                            className="border rounded p-3 bg-white dark:bg-gray-900"
                        >
                            <div className="flex gap-2 mb-3">
                                <input
                                    type="text"
                                    value={model.name}
                                    onChange={(e) => {
                                        dispatch(
                                            updateNestedModel({
                                                modelIndex,
                                                key: "name",
                                                value: e.target.value,
                                            }),
                                        );
                                    }}
                                    className="flex-1 border rounded p-2 text-sm"
                                    placeholder="Object/Array name"
                                />
                                <select
                                    value={model.type}
                                    onChange={(e) => {
                                        dispatch(
                                            updateNestedModel({
                                                modelIndex,
                                                key: "type",
                                                value: e.target.value,
                                            }),
                                        );
                                    }}
                                    className="w-32 border rounded p-2 text-sm"
                                >
                                    <option value="object">Object</option>
                                    <option value="array">Array</option>
                                </select>
                            </div>

                            {model.fields.map((field, fieldIndex) => (
                                <div
                                    key={fieldIndex}
                                    className="flex gap-2 ml-4 mb-2"
                                >
                                    <input
                                        type="text"
                                        value={field.name}
                                        onChange={(e) => {
                                            dispatch(
                                                updateNestedField({
                                                    modelIndex,
                                                    fieldIndex,
                                                    key: "name",
                                                    value: e.target.value,
                                                }),
                                            );
                                        }}
                                        className="flex-1 border rounded p-1 text-sm"
                                        placeholder="Field name"
                                    />
                                    <input
                                        type="text"
                                        value={field.value}
                                        onChange={(e) => {
                                            dispatch(
                                                updateNestedField({
                                                    modelIndex,
                                                    fieldIndex,
                                                    key: "value",
                                                    value: e.target.value,
                                                }),
                                            );
                                        }}
                                        className="flex-1 border rounded p-1 text-sm"
                                        placeholder="Value"
                                    />
                                </div>
                            ))}
                        </div>
                    ))}
                </div>
            </div>

            <div className="flex gap-3">
                <button
                    onClick={handleSaveMock}
                    className="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700"
                >
                    Save Mock Model
                </button>
                <button
                    onClick={handlePreviewWithMock}
                    className="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700"
                >
                    Preview with Mock
                </button>
                <button
                    onClick={() => dispatch(resetMock())}
                    className="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
                >
                    Reset
                </button>
            </div>

            <div className="mt-4 p-3 bg-gray-100 dark:bg-gray-900 rounded text-sm">
                <h5 className="font-medium mb-2">
                    Generated Context Structure:
                </h5>
                <pre className="text-xs overflow-auto">
                    {JSON.stringify(generateMockData(mockData), null, 2)}
                </pre>
            </div>
        </div>
    );
}
