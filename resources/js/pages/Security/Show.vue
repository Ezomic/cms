<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { ShieldCheck, Plus } from 'lucide-vue-next';

interface Passkey {
    id: number;
    name: string;
    authenticator: string;
    last_used: string;
}
defineProps<{ passkeys: Passkey[] }>();

const error = ref('');
const supported =
    typeof window !== 'undefined' &&
    typeof window.PublicKeyCredential !== 'undefined' &&
    typeof PublicKeyCredential.parseCreationOptionsFromJSON === 'function';

const csrf = () => document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';

const addPasskey = async () => {
    error.value = '';
    const name = prompt('Name this passkey (e.g. "MacBook Touch ID")');
    if (!name) return;
    try {
        const optionsRes = await fetch('/user/passkeys/options', { headers: { Accept: 'application/json' } });
        if (!optionsRes.ok) throw new Error();
        const { options } = await optionsRes.json();
        const publicKey = PublicKeyCredential.parseCreationOptionsFromJSON(options);
        const credential = (await navigator.credentials.create({ publicKey })) as PublicKeyCredential & { toJSON(): unknown };
        const storeRes = await fetch('/user/passkeys', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf() },
            body: JSON.stringify({ name, credential: credential.toJSON() }),
        });
        if (!storeRes.ok) throw new Error();
        router.reload({ only: ['passkeys'] });
    } catch {
        error.value = 'Could not register the passkey. Please try again.';
    }
};

const removePasskey = async (id: number) => {
    if (!confirm('Remove this passkey?')) return;
    error.value = '';
    try {
        const res = await fetch(`/user/passkeys/${id}`, {
            method: 'DELETE',
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf() },
        });
        if (!res.ok) throw new Error();
        router.reload({ only: ['passkeys'] });
    } catch {
        error.value = 'Could not remove the passkey. Please try again.';
    }
};
</script>

<template>
    <Head title="Security" />
    <AdminLayout title="Security">
        <div class="max-w-xl">
            <p class="text-muted-foreground mb-4 text-sm">
                Sign in with a passkey instead of a password — fingerprint, face, or device PIN.
            </p>

            <div v-if="error" class="border-destructive/30 bg-destructive/10 text-destructive mb-4 rounded-lg border px-4 py-2.5 text-sm">
                {{ error }}
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Passkeys</CardTitle>
                    <Button v-if="supported" size="sm" @click="addPasskey"><Plus class="size-4" /> Add passkey</Button>
                </CardHeader>
                <CardContent class="p-0">
                    <div v-if="passkeys.length" class="flex flex-col">
                        <div
                            v-for="pk in passkeys"
                            :key="pk.id"
                            class="border-border flex items-center gap-3 border-b px-5 py-3.5 last:border-b-0"
                        >
                            <ShieldCheck class="text-primary size-[18px]" />
                            <div class="min-w-0 flex-1">
                                <div class="font-display text-sm font-semibold">{{ pk.name }}</div>
                                <div class="text-muted-foreground font-mono text-[11px]">{{ pk.authenticator }} · {{ pk.last_used }}</div>
                            </div>
                            <Button
                                variant="ghost"
                                size="sm"
                                class="text-destructive hover:bg-destructive/10 hover:text-destructive"
                                @click="removePasskey(pk.id)"
                                >Remove</Button
                            >
                        </div>
                    </div>
                    <p v-else class="text-muted-foreground px-5 py-6 text-sm">No passkeys registered yet.</p>
                    <p v-if="!supported" class="text-muted-foreground px-5 py-4 text-xs">Your browser doesn't support passkeys.</p>
                </CardContent>
            </Card>

            <p class="text-muted-foreground mt-4 text-xs">No passwords are stored. Sign in uses passkeys or a one-time email code.</p>
        </div>
    </AdminLayout>
</template>
