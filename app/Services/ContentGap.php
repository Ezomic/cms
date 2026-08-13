<?php

declare(strict_types=1);

namespace App\Services;

/**
 * One record with something missing, plus the list of what.
 *
 * A value object rather than an array shape: the gap lists are built
 * conditionally, and passing them around as arrays makes their inferred type a
 * different literal shape at every call site, which static analysis then
 * cannot reconcile with a shared Collection type.
 */
final readonly class ContentGap
{
    /**
     * @param  list<string>  $gaps
     */
    public function __construct(
        public string $type,
        public string $label,
        public string $editUrl,
        public array $gaps,
    ) {}

    /**
     * @return array{type: string, label: string, edit_url: string, gaps: list<string>}
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'label' => $this->label,
            'edit_url' => $this->editUrl,
            'gaps' => $this->gaps,
        ];
    }
}
