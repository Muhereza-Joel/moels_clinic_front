import { useState, useEffect } from "react";
import { DndProvider } from "react-dnd";
import { HTML5Backend } from "react-dnd-html5-backend";
import { Provider } from "react-redux";

import { store } from "../../store";

import TemplateListView from "./views/TemplateListView";
import EditorView from "./views/EditorView";
import MockView from "./views/MockView";
import PreviewView from "./views/PreviewView";
import BuilderHeader from "./layout/BuilderHeader";

import { useAppSelector } from "@/hooks/useAppSelector";
import { useAppDispatch } from "@/hooks/useAppDispatch";
import {
    selectSelectedTemplate,
    selectShowTemplateList,
    selectIsLoading,
    clearSelectedTemplate,
    createNewTemplate,
    fetchTemplate,
    setTemplates,
} from "@/features/template/templateSlice";

export default function TemplateBuilder(props) {
    const dispatch = useAppDispatch();
    const selectedTemplate = useAppSelector(selectSelectedTemplate);
    const showTemplateList = useAppSelector(selectShowTemplateList);
    const isLoading = useAppSelector(selectIsLoading);

    const [activeTab, setActiveTab] = useState("editor");

    // Initialize templates from props
    useEffect(() => {
        if (props.templates) {
            dispatch(setTemplates(props.templates));
        }
    }, [props.templates, dispatch]);

    // Fetch template if UUID provided
    useEffect(() => {
        if (props.templateUuid && !selectedTemplate) {
            dispatch(fetchTemplate(props.templateUuid));
        }
    }, [props.templateUuid, selectedTemplate, dispatch]);

    // Set initial template if provided
    useEffect(() => {
        if (props.initialTemplate && !selectedTemplate) {
            dispatch(setSelectedTemplate(props.initialTemplate));
        }
    }, [props.initialTemplate, selectedTemplate, dispatch]);

    if (isLoading) return <p className="p-6">Loading...</p>;

    if (showTemplateList) {
        return (
            <TemplateListView
                onSelect={(template) => dispatch(fetchTemplate(template.uuid))}
                onCreate={() => dispatch(createNewTemplate())}
            />
        );
    }

    return (
        <DndProvider backend={HTML5Backend}>
            <div className="max-w-7xl mx-auto p-6">
                <BuilderHeader
                    template={selectedTemplate}
                    activeTab={activeTab}
                    onTabChange={setActiveTab}
                    onBack={() => dispatch(clearSelectedTemplate())}
                />

                {activeTab === "editor" && <EditorView />}
                {activeTab === "mock" && <MockView />}
                {activeTab === "preview" && <PreviewView />}
            </div>
        </DndProvider>
    );
}
