import React from "react";
import { useAppSelector } from "@/hooks/useAppSelector";
import { useAppDispatch } from "@/hooks/useAppDispatch";
import {
    selectPreviewUrl,
    selectPreviewLoading,
    generatePreview,
    clearPreview,
} from "@/features/preview/previewSlice";
import { selectSelectedTemplate } from "@/features/template/templateSlice";

export default function PreviewView() {
    const dispatch = useAppDispatch();
    const previewUrl = useAppSelector(selectPreviewUrl);
    const isLoading = useAppSelector(selectPreviewLoading);
    const selectedTemplate = useAppSelector(selectSelectedTemplate);

    const handleGeneratePreview = () => {
        if (selectedTemplate?.uuid) {
            dispatch(
                generatePreview({
                    templateUuid: selectedTemplate.uuid,
                    useMock: false,
                }),
            );
        }
    };

    // Clean up on unmount
    React.useEffect(() => {
        return () => {
            dispatch(clearPreview());
        };
    }, [dispatch]);

    return (
        <div className="space-y-4">
            <button
                onClick={handleGeneratePreview}
                disabled={isLoading || !selectedTemplate}
                className="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                {isLoading ? "Generating..." : "Generate Preview"}
            </button>

            {previewUrl && (
                <div className="border rounded-lg overflow-hidden">
                    <div className="bg-gray-100 dark:bg-gray-800 p-3 flex justify-between items-center">
                        <span className="text-sm font-medium">Preview</span>
                        <a
                            href={previewUrl}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700"
                        >
                            Open in New Tab
                        </a>
                    </div>
                    <iframe
                        src={previewUrl}
                        className="w-full h-[600px] border-0"
                        title="Template Preview"
                    />
                </div>
            )}
        </div>
    );
}
