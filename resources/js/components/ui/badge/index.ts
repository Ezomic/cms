import { cva, type VariantProps } from 'class-variance-authority';

export { default as Badge } from './Badge.vue';

export const badgeVariants = cva('inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 font-mono text-[11px] font-medium', {
    variants: {
        variant: {
            default: 'bg-accent text-accent-foreground',
            success: 'bg-success/10 text-success',
            muted: 'bg-muted text-muted-foreground',
            destructive: 'bg-destructive/10 text-destructive',
            outline: 'border border-input text-muted-foreground',
        },
    },
    defaultVariants: { variant: 'default' },
});

export type BadgeVariants = VariantProps<typeof badgeVariants>;
