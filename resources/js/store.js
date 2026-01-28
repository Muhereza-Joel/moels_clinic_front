import { configureStore } from "@reduxjs/toolkit";
import templateReducer from "./features/template/templateSlice";
import sectionReducer from "./features/section/sectionSlice";
import previewReducer from "./features/preview/previewSlice";
import mockReducer from "./features/mock/mockSlice";

export const store = configureStore({
    reducer: {
        template: templateReducer,
        section: sectionReducer,
        preview: previewReducer,
        mock: mockReducer,
    },
    middleware: (getDefaultMiddleware) =>
        getDefaultMiddleware({
            serializableCheck: {
                ignoredActions: ["preview/setPreviewUrl"],
                ignoredPaths: ["preview.previewUrl"],
            },
        }),
});
