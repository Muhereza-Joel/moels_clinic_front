import TemplateBuilder from "@/Components/TemplateBuilder";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head } from "@inertiajs/react";

export default function Dashboard({
    templates,
    records,
    template,
    templateUuid,
}) {
    return (
        <AuthenticatedLayout>
            <Head title="Template Builder" />

            <TemplateBuilder
                initialTemplate={template}
                templates={templates}
                records={records}
                templateUuid={templateUuid}
            />
        </AuthenticatedLayout>
    );
}
