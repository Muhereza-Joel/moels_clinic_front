import { createSlice } from "@reduxjs/toolkit";
import { updateTemplateLayout, fetchTemplate } from "../template/templateSlice";

/* -------------------- Helper -------------------- */
/**
 * Factory function to create a default block by type.
 */
const createDefaultBlock = (type) => {
    switch (type) {
        case "text":
            return { type: "text", content: "" };

        case "conditional":
            return {
                type: "conditional",
                condition: null,
                trueBlock: { type: "text", content: "Condition is true" },
                falseBlock: { type: "text", content: "Condition is false" },
            };

        case "table":
            return {
                type: "table",
                columns: ["Column 1", "Column 2"],
                rows: [["Value 1", "Value 2"]],
            };

        case "qrcode":
            return { type: "qrcode", value: "" };

        case "chart":
            return {
                type: "chart",
                data: { type: "bar", values: [10, 20, 30, 40, 50] },
            };

        default:
            return { type: "text", content: "" };
    }
};

/* -------------------- Slice -------------------- */
const initialState = {
    sections: [],
};

const sectionSlice = createSlice({
    name: "section",
    initialState,
    reducers: {
        setSections: (state, action) => {
            state.sections = action.payload || [];
        },
        addSection: (state) => {
            state.sections.push({
                title: `Section ${state.sections.length + 1}`,
                grid: { columns: 2, items: [] },
            });
        },
        updateSection: (state, action) => {
            const { index, data } = action.payload;
            if (state.sections[index]) {
                state.sections[index] = { ...state.sections[index], ...data };
            }
        },
        removeSection: (state, action) => {
            const index = action.payload;
            if (index >= 0 && index < state.sections.length) {
                state.sections.splice(index, 1);
            }
        },
        addBlock: (state, action) => {
            const { sectionIndex, type } = action.payload;
            if (state.sections[sectionIndex]) {
                const block = createDefaultBlock(type);
                state.sections[sectionIndex].grid.items.push(block);
            }
        },
        updateBlock: (state, action) => {
            const { sectionIndex, blockIndex, block } = action.payload;
            if (state.sections[sectionIndex]?.grid.items[blockIndex]) {
                state.sections[sectionIndex].grid.items[blockIndex] = block;
            }
        },
        removeBlock: (state, action) => {
            const { sectionIndex, blockIndex } = action.payload;
            if (state.sections[sectionIndex]?.grid.items[blockIndex]) {
                state.sections[sectionIndex].grid.items.splice(blockIndex, 1);
            }
        },
        moveBlock: (state, action) => {
            const { sectionIndex, dragIndex, hoverIndex } = action.payload;
            const items = state.sections[sectionIndex]?.grid.items;
            if (items && dragIndex >= 0 && hoverIndex >= 0) {
                const [draggedItem] = items.splice(dragIndex, 1);
                items.splice(hoverIndex, 0, draggedItem);
            }
        },
    },
});

/* -------------------- Thunks -------------------- */
/**
 * Sync current sections state back into the selected template layout.
 */
/**
 * Sync current sections state back into the selected template layout
 * while preserving other layout properties (footer, margins, etc.)
 */
export const syncSectionsToTemplate = () => (dispatch, getState) => {
    const state = getState();
    const currentLayout = state.template.selectedTemplate?.layout || {}; // Get existing layout

    const updatedLayout = {
        ...currentLayout, // Preserve footer, orientation, page_size
        sections: state.section.sections, // Update only the sections
    };

    dispatch(updateTemplateLayout(updatedLayout));
};

/**
 * Load a template by UUID and hydrate the section slice.
 */
export const loadTemplateSections = (uuid) => async (dispatch) => {
    const result = await dispatch(fetchTemplate(uuid));

    if (fetchTemplate.fulfilled.match(result)) {
        const template = result.payload;
        if (template.layout?.sections) {
            dispatch(setSections(template.layout.sections));
        }
    }
};

/* -------------------- Exports -------------------- */
export const {
    setSections,
    addSection,
    updateSection,
    removeSection,
    addBlock,
    updateBlock,
    removeBlock,
    moveBlock,
} = sectionSlice.actions;

export const selectSections = (state) => state.section.sections;

export default sectionSlice.reducer;
