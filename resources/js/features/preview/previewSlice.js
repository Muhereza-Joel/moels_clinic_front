import { createSlice, createAsyncThunk } from "@reduxjs/toolkit";

export const generatePreview = createAsyncThunk(
    "preview/generatePreview",
    async (
        { templateUuid, useMock = false, mockData = null },
        { getState },
    ) => {
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content");

        const res = await fetch(
            `/templates/${templateUuid}/preview${useMock ? "-mock" : ""}`,
            {
                method: "POST",
                credentials: "same-origin",
                headers: {
                    "Content-Type": "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN": csrfToken,
                },
                body: JSON.stringify({
                    mock_data: useMock ? mockData : null,
                }),
            },
        );

        if (!res.ok) {
            throw new Error("Failed to generate preview");
        }

        const blob = await res.blob();
        return URL.createObjectURL(blob);
    },
);

const previewSlice = createSlice({
    name: "preview",
    initialState: {
        previewUrl: "",
        isLoading: false,
        error: null,
    },
    reducers: {
        setPreviewUrl: (state, action) => {
            // Revoke previous URL to prevent memory leaks
            if (state.previewUrl) {
                URL.revokeObjectURL(state.previewUrl);
            }
            state.previewUrl = action.payload;
        },
        clearPreview: (state) => {
            if (state.previewUrl) {
                URL.revokeObjectURL(state.previewUrl);
            }
            state.previewUrl = "";
        },
    },
    extraReducers: (builder) => {
        builder
            .addCase(generatePreview.pending, (state) => {
                state.isLoading = true;
                state.error = null;
            })
            .addCase(generatePreview.fulfilled, (state, action) => {
                state.isLoading = false;
                state.previewUrl = action.payload;
            })
            .addCase(generatePreview.rejected, (state, action) => {
                state.isLoading = false;
                state.error = action.error.message;
            });
    },
});

export const { setPreviewUrl, clearPreview } = previewSlice.actions;

export const selectPreviewUrl = (state) => state.preview.previewUrl;
export const selectPreviewLoading = (state) => state.preview.isLoading;
export const selectPreviewError = (state) => state.preview.error;

export default previewSlice.reducer;
