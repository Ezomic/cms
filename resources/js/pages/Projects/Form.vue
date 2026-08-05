<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { UploadCloud, X, Eye } from 'lucide-vue-next';

interface ProjectData {
    id: number;
    name: string;
    slug: string | null;
    client_name: string | null;
    year: string | null;
    github_url: string | null;
    description: string | null;
    outcome: string | null;
    body: string | null;
    tags: string | null;
    published: boolean;
    meta_title: string | null;
    meta_description: string | null;
    image_url: string | null;
    image_alt: string | null;
    description_nl: string | null;
    outcome_nl: string | null;
    body_nl: string | null;
    image_alt_nl: string | null;
    meta_title_nl: string | null;
    meta_description_nl: string | null;
    images: { id: number; url: string }[];
    preview_url: string;
}

const props = defineProps<{ project: ProjectData | null }>();
const isEdit = computed(() => props.project !== null);
const lang = ref<'en' | 'nl'>('en');

const form = useForm({
    name: props.project?.name ?? '',
    slug: props.project?.slug ?? '',
    client_name: props.project?.client_name ?? '',
    year: props.project?.year ?? '',
    github_url: props.project?.github_url ?? '',
    description: props.project?.description ?? '',
    outcome: props.project?.outcome ?? '',
    body: props.project?.body ?? '',
    tags: props.project?.tags ?? '',
    published: props.project?.published ?? false,
    meta_title: props.project?.meta_title ?? '',
    meta_description: props.project?.meta_description ?? '',
    image_alt: props.project?.image_alt ?? '',
    description_nl: props.project?.description_nl ?? '',
    outcome_nl: props.project?.outcome_nl ?? '',
    body_nl: props.project?.body_nl ?? '',
    image_alt_nl: props.project?.image_alt_nl ?? '',
    meta_title_nl: props.project?.meta_title_nl ?? '',
    meta_description_nl: props.project?.meta_description_nl ?? '',
    image: null as File | null,
    gallery: [] as File[],
    remove_images: [] as number[],
});

const imagePreview = ref<string | null>(props.project?.image_url ?? null);
const onImage = (e: Event) => {
    const file = (e.target as HTMLInputElement).files?.[0] ?? null;
    form.image = file;
    imagePreview.value = file ? URL.createObjectURL(file) : (props.project?.image_url ?? null);
};
const onGallery = (e: Event) => {
    form.gallery = Array.from((e.target as HTMLInputElement).files ?? []);
};
const toggleRemove = (id: number) => {
    const i = form.remove_images.indexOf(id);
    if (i === -1) form.remove_images.push(id);
    else form.remove_images.splice(i, 1);
};

const submit = () => {
    if (isEdit.value) {
        form.transform((data) => ({ ...data, _method: 'put' })).post(`/admin/projects/${props.project!.id}`, { forceFormData: true });
    } else {
        form.post('/admin/projects', { forceFormData: true });
    }
};
</script>

<template>
    <Head :title="isEdit ? 'Edit project' : 'New project'" />
    <AdminLayout :title="isEdit ? 'Edit project' : 'New project'">
        <template #actions>
            <Button as="a" href="/admin/projects" variant="ghost">Cancel</Button>
            <Button
                v-if="project"
                as="a"
                :href="project.preview_url"
                target="_blank"
                rel="noopener"
                variant="ghost"
                title="Signed link, valid for 24 hours"
            >
                <Eye class="mr-1.5 size-4" /> Preview
            </Button>
            <Button :disabled="form.processing" @click="submit">{{ isEdit ? 'Save changes' : 'Create project' }}</Button>
        </template>

        <form class="grid grid-cols-1 items-start gap-5 lg:grid-cols-[1fr_320px]" @submit.prevent="submit">
            <div class="flex flex-col gap-5">
                <Card>
                    <CardContent class="flex flex-col gap-4">
                        <div>
                            <Label for="name">Name</Label>
                            <Input id="name" v-model="form.name" />
                            <p v-if="form.errors.name" class="text-destructive mt-1 text-xs">{{ form.errors.name }}</p>
                        </div>
                        <div>
                            <Label for="slug">Slug</Label>
                            <Input id="slug" v-model="form.slug" class="font-mono" placeholder="auto from name" />
                            <p v-if="form.errors.slug" class="text-destructive mt-1 text-xs">{{ form.errors.slug }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div><Label for="client">Client</Label><Input id="client" v-model="form.client_name" /></div>
                            <div><Label for="year">Year</Label><Input id="year" v-model="form.year" /></div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Content</CardTitle>
                        <div class="flex gap-1">
                            <button type="button" class="lt" :class="lang === 'en' ? 'lt-on' : ''" @click="lang = 'en'">EN</button>
                            <button type="button" class="lt" :class="lang === 'nl' ? 'lt-on' : ''" @click="lang = 'nl'">NL</button>
                        </div>
                    </CardHeader>
                    <CardContent class="flex flex-col gap-4">
                        <template v-if="lang === 'en'">
                            <div><Label for="desc">Short description</Label><Textarea id="desc" v-model="form.description" /></div>
                            <div><Label for="body">Body (case study)</Label><Textarea id="body" v-model="form.body" :rows="8" /></div>
                            <div><Label for="outcome">Outcome</Label><Input id="outcome" v-model="form.outcome" /></div>
                        </template>
                        <template v-else>
                            <div>
                                <Label for="desc_nl">Korte omschrijving</Label><Textarea id="desc_nl" v-model="form.description_nl" />
                            </div>
                            <div>
                                <Label for="body_nl">Body (case study)</Label><Textarea id="body_nl" v-model="form.body_nl" :rows="8" />
                            </div>
                            <div><Label for="outcome_nl">Resultaat</Label><Input id="outcome_nl" v-model="form.outcome_nl" /></div>
                        </template>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader><CardTitle>Media</CardTitle></CardHeader>
                    <CardContent class="flex flex-col gap-4">
                        <div>
                            <Label>Cover image</Label>
                            <label
                                class="border-input bg-background text-muted-foreground hover:border-primary flex cursor-pointer flex-col items-center gap-2 rounded-md border border-dashed px-4 py-6 text-center text-sm"
                            >
                                <img
                                    v-if="imagePreview"
                                    :src="imagePreview"
                                    alt=""
                                    class="border-border mb-1 h-28 rounded-md border object-cover"
                                />
                                <UploadCloud v-else class="size-6" />
                                <span>Drop an image or click to upload · scaled to 1600px</span>
                                <input type="file" accept="image/*" class="hidden" @change="onImage" />
                            </label>
                            <p v-if="form.errors.image" class="text-destructive mt-1 text-xs">{{ form.errors.image }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div><Label for="alt">Image alt (EN)</Label><Input id="alt" v-model="form.image_alt" /></div>
                            <div><Label for="alt_nl">Image alt (NL)</Label><Input id="alt_nl" v-model="form.image_alt_nl" /></div>
                        </div>
                        <div v-if="project?.images.length">
                            <Label>Gallery</Label>
                            <div class="flex flex-wrap gap-2">
                                <div v-for="img in project.images" :key="img.id" class="relative">
                                    <img
                                        :src="img.url"
                                        alt=""
                                        class="border-border h-16 w-24 rounded-md border object-cover"
                                        :class="form.remove_images.includes(img.id) ? 'opacity-30' : ''"
                                    />
                                    <button
                                        type="button"
                                        class="bg-destructive text-destructive-foreground absolute -top-1.5 -right-1.5 flex size-5 items-center justify-center rounded-full"
                                        @click="toggleRemove(img.id)"
                                    >
                                        <X class="size-3" />
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div>
                            <Label for="gallery">Add gallery images</Label>
                            <input
                                id="gallery"
                                type="file"
                                accept="image/*"
                                multiple
                                class="text-muted-foreground file:border-input file:bg-card block w-full text-sm file:mr-3 file:rounded-md file:border file:px-3 file:py-1.5 file:text-sm"
                                @change="onGallery"
                            />
                            <p v-if="form.errors.gallery" class="text-destructive mt-1 text-xs">{{ form.errors.gallery }}</p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div class="flex flex-col gap-5">
                <Card>
                    <CardContent class="flex flex-col gap-4">
                        <div>
                            <Label>Status</Label>
                            <div class="flex items-center gap-2.5">
                                <Switch v-model="form.published" />
                                <span class="text-sm">{{ form.published ? 'Published' : 'Draft' }}</span>
                            </div>
                        </div>
                        <div>
                            <Label for="gh">GitHub URL</Label>
                            <Input id="gh" v-model="form.github_url" class="font-mono" placeholder="https://github.com/…" />
                            <p v-if="form.errors.github_url" class="text-destructive mt-1 text-xs">{{ form.errors.github_url }}</p>
                        </div>
                        <div>
                            <Label for="tags">Tags</Label>
                            <Input id="tags" v-model="form.tags" placeholder="Laravel, Vue, TypeScript" />
                            <p class="text-muted-foreground mt-1 text-xs">Comma-separated.</p>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>SEO</CardTitle>
                        <div class="flex gap-1">
                            <button type="button" class="lt" :class="lang === 'en' ? 'lt-on' : ''" @click="lang = 'en'">EN</button>
                            <button type="button" class="lt" :class="lang === 'nl' ? 'lt-on' : ''" @click="lang = 'nl'">NL</button>
                        </div>
                    </CardHeader>
                    <CardContent class="flex flex-col gap-4">
                        <template v-if="lang === 'en'">
                            <div><Label for="mt">Meta title</Label><Input id="mt" v-model="form.meta_title" /></div>
                            <div>
                                <Label for="md">Meta description</Label><Textarea id="md" v-model="form.meta_description" :rows="3" />
                            </div>
                        </template>
                        <template v-else>
                            <div><Label for="mt_nl">Meta title</Label><Input id="mt_nl" v-model="form.meta_title_nl" /></div>
                            <div>
                                <Label for="md_nl">Meta description</Label
                                ><Textarea id="md_nl" v-model="form.meta_description_nl" :rows="3" />
                            </div>
                        </template>
                    </CardContent>
                </Card>
            </div>
        </form>
    </AdminLayout>
</template>

<style scoped>
@reference '../../../css/app.css';
.lt {
    @apply border-border text-muted-foreground cursor-pointer rounded-md border px-2.5 py-1 font-mono text-[11px];
}
.lt-on {
    @apply border-foreground bg-foreground text-background;
}
</style>
