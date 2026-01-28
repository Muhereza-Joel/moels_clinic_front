import { createSlice } from "@reduxjs/toolkit";

const mockSlice = createSlice({
    name: "mock",
    initialState: {
        modelName: "Patient",
        fields: [
            { name: "name", type: "string", value: "John Doe" },
            { name: "date_of_birth", type: "date", value: "1990-01-01" },
            { name: "mrn", type: "string", value: "KLA-PT-0F3A9K" },
            { name: "age", type: "number", value: "34" },
            { name: "is_active", type: "boolean", value: "true" },
        ],
        nestedModels: [
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
        ],
    },
    reducers: {
        setModelName: (state, action) => {
            state.modelName = action.payload;
        },
        setFields: (state, action) => {
            state.fields = action.payload;
        },
        addField: (state) => {
            state.fields.push({ name: "", type: "string", value: "" });
        },
        updateField: (state, action) => {
            const { index, key, value } = action.payload;
            if (state.fields[index]) {
                state.fields[index][key] = value;
            }
        },
        removeField: (state, action) => {
            const index = action.payload;
            state.fields.splice(index, 1);
        },
        setNestedModels: (state, action) => {
            state.nestedModels = action.payload;
        },
        addNestedModel: (state) => {
            state.nestedModels.push({
                name: "new_object",
                type: "object",
                fields: [{ name: "field1", type: "string", value: "value" }],
            });
        },
        updateNestedModel: (state, action) => {
            const { modelIndex, key, value } = action.payload;
            if (state.nestedModels[modelIndex]) {
                state.nestedModels[modelIndex][key] = value;
            }
        },
        updateNestedField: (state, action) => {
            const { modelIndex, fieldIndex, key, value } = action.payload;
            if (state.nestedModels[modelIndex]?.fields[fieldIndex]) {
                state.nestedModels[modelIndex].fields[fieldIndex][key] = value;
            }
        },
        resetMock: (state) => {
            state.modelName = "Patient";
            state.fields = [
                { name: "name", type: "string", value: "John Doe" },
                { name: "date_of_birth", type: "date", value: "1990-01-01" },
                { name: "mrn", type: "string", value: "KLA-PT-0F3A9K" },
                { name: "age", type: "number", value: "34" },
                { name: "is_active", type: "boolean", value: "true" },
            ];
            state.nestedModels = [
                {
                    name: "organization",
                    type: "object",
                    fields: [
                        { name: "name", type: "string", value: "Demo Clinic" },
                        {
                            name: "address",
                            type: "string",
                            value: "123 Main St",
                        },
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
                                {
                                    name: "date",
                                    type: "date",
                                    value: "2024-01-15",
                                },
                                {
                                    name: "diagnosis",
                                    type: "string",
                                    value: "Influenza",
                                },
                            ],
                        },
                    ],
                },
            ];
        },
    },
});

export const {
    setModelName,
    setFields,
    addField,
    updateField,
    removeField,
    setNestedModels,
    addNestedModel,
    updateNestedModel,
    updateNestedField,
    resetMock,
} = mockSlice.actions;

export const generateMockData = (state) => {
    const { modelName, fields, nestedModels } = state.mock;

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

export const selectMockData = (state) => state.mock;

export default mockSlice.reducer;
