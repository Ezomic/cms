<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';

interface Data {
    id: number; title: string; slug: string | null; excerpt: string | null; body: string | null;
    published: boolean; meta_title: string | null; meta_description: string | null;
    title_nl: string | null; excerpt_nl: string | null; body_nl: string | null;
    meta_title_nl: string | null; meta_description_nl: string | null;
}
const props = defineProps<{ post: Data | null }>();
const isEdit = computed(() => props.post !== null);
const lang = ref<'en' | 'nl'>('en');

const form = useForm({
    title: props.post?.title ?? '',
    slug: props.post?.slug ?? '',
    excerpt: props.post?.excerpt ?? '',
    body: props.post?.body ?? '',
    published: props.post?.published ?? false,
    meta_title: props.post?.meta_title ?? '',
    meta_description: props.post?.meta_description ?? '',
    title_nl: props.post?.title_nl ?? '',
    excerpt_nl: props.post?.excerpt_nl ?? '',
    body_nl: props.post?.body_nl ?? '',
    meta_title_nl: props.post?.meta_title_nl ?? '',
    meta_description_nl: props.post?.meta_description_nl ?? '',
});

const submit = () => isEdit.value ? form.put(`/admin/posts/${props.post!.id}`) : form.post('/admin/posts');
</script>

<template>
    <Head :title="isEdit ? 'Edit post' : 'New post'" />
    <AdminLayout :title="isEdit ? 'Edit post' : 'New post'">
        <template #actions>
            <Button as="a" href="/admin/posts" variant="ghost">Cancel</Button>
            <Button :disabled="form.processing" @click="submit">{{ isEdit ? 'Save changes' : 'Create post' }}</Button>
        </template>

        <form class="grid grid-cols-1 items-start gap-5 lg:grid-cols-[1fr_300px]" @submit.prevent="submit">
            <div class="flex flex-col gap-5">
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
                            <div><Label for="title">Title</Label><Input id="title" v-model="form.title" /><p v-if="form.errors.title" class="mt-1 text-xs text-destructive">{{ form.errors.title }}</p></div>
                            <div><Label for="excerpt">Excerpt</Label><Textarea id="excerpt" v-model="form.excerpt" :rows="2" /></div>
                            <div><Label for="body">Body</Label><Textarea id="body" v-model="form.body" :rows="12" /></div>
                        </template>
                        <template v-else>
                            <div><Label for="title_nl">Titel</Label><Input id="title_nl" v-model="form.title_nl" /></div>
                            <div><Label for="excerpt_nl">Samenvatting</Label><Textarea id="excerpt_nl" v-model="form.excerpt_nl" :rows="2" /></div>
                            <div><Label for="body_nl">Body</Label><Textarea id="body_nl" v-model="form.body_nl" :rows="12" /></div>
                        </template>
                    </CardContent>
                </Card>
            </div>

            <div class="flex flex-col gap-5">
                <Card>
                    <CardContent class="flex flex-col gap-4">
                        <div>
                            <Label>Status</Label>
                            <div class="flex items-center gap-2.5"><Switch v-model="form.published" /><span class="text-sm">{{ form.published ? 'Published' : 'Draft' }}</span></div>
                        </div>
                        <div>
                            <Label for="slug">Slug</Label>
                            <Input id="slug" v-model="form.slug" class="font-mono" placeholder="auto from title" />
                            <p v-if="form.errors.slug" class="mt-1 text-xs text-destructive">{{ form.errors.slug }}</p>
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
                            <div><Label for="md">Meta description</Label><Textarea id="md" v-model="form.meta_description" :rows="3" /></div>
                        </template>
                        <template v-else>
                            <div><Label for="mt_nl">Meta title</Label><Input id="mt_nl" v-model="form.meta_title_nl" /></div>
                            <div><Label for="md_nl">Meta description</Label><Textarea id="md_nl" v-model="form.meta_description_nl" :rows="3" /></div>
                        </template>
                    </CardContent>
                </Card>
            </div>
        </form>
    </AdminLayout>
</template>
