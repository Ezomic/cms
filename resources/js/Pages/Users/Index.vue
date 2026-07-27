<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell } from '@/components/ui/table';
import { Plus } from 'lucide-vue-next';

interface Row { id: number; name: string; email: string; has_passkey: boolean; is_current: boolean }
defineProps<{ users: Row[] }>();

const destroy = (row: Row) => { if (confirm(`Delete admin user "${row.name}"?`)) router.delete(`/admin/users/${row.id}`, { preserveScroll: true }); };
</script>

<template>
    <Head title="Users" />
    <AdminLayout title="Users">
        <template #actions><Button as="a" href="/admin/users/create"><Plus class="size-4" /> Add user</Button></template>

        <Card>
            <Table>
                <TableHeader>
                    <TableRow class="hover:bg-transparent">
                        <TableHead>User</TableHead>
                        <TableHead>Email</TableHead>
                        <TableHead>Auth</TableHead>
                        <TableHead class="text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="row in users" :key="row.id">
                        <TableCell>
                            <div class="flex items-center gap-2.5">
                                <span class="flex size-7 items-center justify-center rounded-lg bg-accent font-display text-xs font-bold text-accent-foreground">{{ row.name.slice(0, 2).toUpperCase() }}</span>
                                <span class="font-display text-sm font-semibold">{{ row.name }}<span v-if="row.is_current" class="ml-1.5 font-mono text-[10px] font-normal text-muted-foreground">(you)</span></span>
                            </div>
                        </TableCell>
                        <TableCell><span class="font-mono text-xs text-muted-foreground">{{ row.email }}</span></TableCell>
                        <TableCell>
                            <Badge v-if="row.has_passkey" variant="success">Passkey</Badge>
                            <Badge v-else variant="muted">Email code</Badge>
                        </TableCell>
                        <TableCell>
                            <div class="flex justify-end gap-1">
                                <Button as="a" :href="`/admin/users/${row.id}/edit`" variant="ghost" size="sm">Edit</Button>
                                <Button v-if="!row.is_current" variant="ghost" size="sm" class="text-destructive hover:bg-destructive/10 hover:text-destructive" @click="destroy(row)">Delete</Button>
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </Card>
    </AdminLayout>
</template>
