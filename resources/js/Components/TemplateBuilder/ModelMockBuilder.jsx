// ModelMockBuilder.jsx
import { useState } from "react";

export default function ModelMockBuilder({ onMockSave, onMockPreview }) {
    const [modelName, setModelName] = useState("Patient");
    const [fields, setFields] = useState([
        { name: "name", type: "string", value: "John Doe" },
        { name: "date_of_birth", type: "date", value: "1990-01-01" },
        { name: "mrn", type: "string", value: "KLA-PT-0F3A9K" },
        { name: "age", type: "number", value: "34" },
        { name: "is_active", type: "boolean", value: "true" },
    ]);
    const [nestedModels, setNestedModels] = useState([
        {
            name: "organization",
            type: "object",
            fields: [
                { name: "name", type: "string", value: "Demo Clinic" },
                { name: "address", type: "string", value: "123 Main St" },
            ],
        },
        {
            name: "visits",
            type: "array",
            fields: [
                {
                    name: "0",
                    type: "object",
                    fields: [
                        { name: "date", type: "date", value: "2024-01-15" },
                        {
                            name: "diagnosis",
                            type: "string",
                            value: "Influenza",
                        },
                    ],
                },
            ],
        },
    ]);

    const addField = () => {
        setFields([...fields, { name: "", type: "string", value: "" }]);
    };

    const updateField = (index, key, value) => {
        const newFields = [...fields];
        newFields[index][key] = value;
        setFields(newFields);
    };

    const removeField = (index) => {
        setFields(fields.filter((_, i) => i !== index));
    };

    const addNestedModel = () => {
        setNestedModels([
            ...nestedModels,
            {
                name: "new_object",
                type: "object",
                fields: [{ name: "field1", type: "string", value: "value" }],
            },
        ]);
    };

    const generateMockData = () => {
        const mockData = {};

        // Add flat fields
        fields.forEach((field) => {
            mockData[field.name] = convertValue(field.type, field.value);
        });

        // Add nested models
        nestedModels.forEach((model) => {
            if (model.type === "object") {
                mockData[model.name] = {};
                model.fields.forEach((field) => {
                    mockData[model.name][field.name] = convertValue(
                        field.type,
                        field.value,
                    );
                });
            } else if (model.type === "array") {
                mockData[model.name] = model.fields.map((item) => {
                    const obj = {};
                    item.fields.forEach((field) => {
                        obj[field.name] = convertValue(field.type, field.value);
                    });
                    return obj;
                });
            }
        });

        return { [modelName.toLowerCase()]: mockData };
    };

    const convertValue = (type, value) => {
        switch (type) {
            case "number":
                return Number(value);
            case "boolean":
                return value === "true";
            case "date":
                return new Date(value).toISOString().split("T")[0];
            case "array":
                return value.split(",").map((v) => v.trim());
            default:
                return value;
        }
    };

    const saveMock = () => {
        const mockData = generateMockData();
        onMockSave(mockData);
    };

    const previewWithMock = () => {
        const mockData = generateMockData();
        onMockPreview(mockData);
    };

    return (
        <div className="border rounded-lg p-4 bg-gray-50 dark:bg-gray-800">
            <h3 className="text-lg font-semibold mb-4 text-gray-900 dark:text-white">
                Model Mock Builder: {modelName}
            </h3>

            <div className="mb-4">
                <label className="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">
                    Model Name
                </label>
                <input
                    type="text"
                    value={modelName}
                    onChange={(e) => setModelName(e.target.value)}
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
                        onClick={addField}
                        className="px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700"
                    >
                        + Add Field
                    </button>
                </div>

                <div className="space-y-3">
                    {fields.map((field, index) => (
                        <div key={index} className="flex gap-2 items-center">
                            <input
                                type="text"
                                value={field.name}
                                onChange={(e) =>
                                    updateField(index, "name", e.target.value)
                                }
                                className="flex-1 border rounded p-2 text-sm"
                                placeholder="Field name"
                            />
                            <select
                                value={field.type}
                                onChange={(e) =>
                                    updateField(index, "type", e.target.value)
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
                                    updateField(index, "value", e.target.value)
                                }
                                className="flex-1 border rounded p-2 text-sm"
                                placeholder="Mock value"
                            />
                            <button
                                onClick={() => removeField(index)}
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
                        onClick={addNestedModel}
                        className="px-3 py-1 text-sm bg-green-600 text-white rounded hover:bg-green-700"
                    >
                        + Add Nested
                    </button>
                </div>

                <div className="space-y-4">
                    {nestedModels.map((model, modelIndex) => (
                        <div
                            key={modelIndex}
                            className="border rounded p-3 bg-white dark:bg-gray-900"
                        >
                            <div className="flex gap-2 mb-3">
                                <input
                                    type="text"
                                    value={model.name}
                                    onChange={(e) => {
                                        const newModels = [...nestedModels];
                                        newModels[modelIndex].name =
                                            e.target.value;
                                        setNestedModels(newModels);
                                    }}
                                    className="flex-1 border rounded p-2 text-sm"
                                    placeholder="Object/Array name"
                                />
                                <select
                                    value={model.type}
                                    onChange={(e) => {
                                        const newModels = [...nestedModels];
                                        newModels[modelIndex].type =
                                            e.target.value;
                                        setNestedModels(newModels);
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
                                            const newModels = [...nestedModels];
                                            newModels[modelIndex].fields[
                                                fieldIndex
                                            ].name = e.target.value;
                                            setNestedModels(newModels);
                                        }}
                                        className="flex-1 border rounded p-1 text-sm"
                                        placeholder="Field name"
                                    />
                                    <input
                                        type="text"
                                        value={field.value}
                                        onChange={(e) => {
                                            const newModels = [...nestedModels];
                                            newModels[modelIndex].fields[
                                                fieldIndex
                                            ].value = e.target.value;
                                            setNestedModels(newModels);
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
                    onClick={saveMock}
                    className="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700"
                >
                    Save Mock Model
                </button>
                <button
                    onClick={previewWithMock}
                    className="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700"
                >
                    Preview with Mock
                </button>
            </div>

            <div className="mt-4 p-3 bg-gray-100 dark:bg-gray-900 rounded text-sm">
                <h5 className="font-medium mb-2">
                    Generated Context Structure:
                </h5>
                <pre className="text-xs overflow-auto">
                    {JSON.stringify(generateMockData(), null, 2)}
                </pre>
            </div>
        </div>
    );
}
