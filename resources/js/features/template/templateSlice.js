import { createSlice, createAsyncThunk } from "@reduxjs/toolkit";

export const fetchTemplate = createAsyncThunk(
    "template/fetchTemplate",
    async (templateUuid) => {
        const response = await fetch(`/api/templates/${templateUuid}`);
        return await response.json();
    },
);

export const createTemplate = createAsyncThunk(
    "template/createTemplate",
    async (templateData) => {
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content");

        const response = await fetch("/api/templates", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
            },
            body: JSON.stringify(templateData),
        });
        return await response.json();
    },
);

export const updateTemplate = createAsyncThunk(
    "template/updateTemplate",
    async ({ uuid, data }) => {
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content");

        const response = await fetch(`/templates/${uuid}`, {
            method: "PUT",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
            },
            body: JSON.stringify(data), // ✅ send full data object
        });
        return await response.json();
    },
);

const templateSlice = createSlice({
    name: "template",
    initialState: {
        selectedTemplate: null,
        templates: [],
        isLoading: false,
        showTemplateList: false,
        error: null,
    },
    reducers: {
        setSelectedTemplate: (state, action) => {
            state.selectedTemplate = action.payload;
            state.showTemplateList = false;
        },
        setTemplates: (state, action) => {
            state.templates = action.payload;
        },
        setShowTemplateList: (state, action) => {
            state.showTemplateList = action.payload;
        },
        updateTemplateLayout: (state, action) => {
            if (state.selectedTemplate) {
                state.selectedTemplate.layout = action.payload;
            }
        },
        clearSelectedTemplate: (state) => {
            state.selectedTemplate = null;
            state.showTemplateList = true;
        },
        createNewTemplate: (state) => {
            state.selectedTemplate = {
                uuid: `temp-${Date.now()}`,
                name: "New Template",
                code: "NEW_TEMPLATE",
                layout: {
                    sections: [],
                },
            };
            state.showTemplateList = false;
        },
    },
    extraReducers: (builder) => {
        builder
            .addCase(fetchTemplate.pending, (state) => {
                state.isLoading = true;
                state.error = null;
            })
            .addCase(fetchTemplate.fulfilled, (state, action) => {
                state.isLoading = false;
                state.selectedTemplate = action.payload;
                state.showTemplateList = false;
            })
            .addCase(fetchTemplate.rejected, (state, action) => {
                state.isLoading = false;
                state.error = action.error.message;
            })
            .addCase(createTemplate.pending, (state) => {
                state.isLoading = true;
            })
            .addCase(createTemplate.fulfilled, (state, action) => {
                state.isLoading = false;
                state.selectedTemplate = action.payload;
                state.templates.push(action.payload);
                state.showTemplateList = false;
            })
            .addCase(updateTemplate.rejected, (state, action) => {
                state.isLoading = false;
                state.error = action.error.message;
            });
    },
});

export const {
    setSelectedTemplate,
    setTemplates,
    setShowTemplateList,
    updateTemplateLayout,
    clearSelectedTemplate,
    createNewTemplate,
} = templateSlice.actions;

export const selectSelectedTemplate = (state) =>
    state.template.selectedTemplate;
export const selectTemplates = (state) => state.template.templates;
export const selectIsLoading = (state) => state.template.isLoading;
export const selectShowTemplateList = (state) =>
    state.template.showTemplateList;

export default templateSlice.reducer;
