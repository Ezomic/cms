<?php

declare(strict_types=1);

namespace App\Services;

use HTMLPurifier;
use HTMLPurifier_Config;

class HtmlSanitizer
{
    private const ALLOWED = 'h1,h2,h3,h4,h5,h6,p,br,hr,strong,b,em,i,u,s,del,ins,sub,sup,small,span,div,blockquote,pre,code,'
        .'ul,ol,li,dl,dt,dd,table,thead,tbody,tfoot,tr,th[colspan|rowspan],td[colspan|rowspan],'
        .'a[href|title|target|rel],img[src|alt|title|width|height]';

    private ?HTMLPurifier $purifier = null;

    /**
     * Allow-list sanitising for admin-authored rich text: normal formatting,
     * links, lists, code and images survive; scripts, event handlers, styles
     * and non-web URL schemes (javascript:, data:) do not.
     */
    public function handle(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return null;
        }

        $clean = trim($this->purifier()->purify($html));

        return $clean === '' ? null : $clean;
    }

    private function purifier(): HTMLPurifier
    {
        if ($this->purifier === null) {
            $config = HTMLPurifier_Config::createDefault();
            $config->set('Core.Encoding', 'UTF-8');
            $config->set('HTML.Allowed', self::ALLOWED);
            $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true, 'tel' => true]);
            $config->set('Attr.AllowedFrameTargets', ['_blank']);
            $config->set('HTML.TargetNoopener', true);
            $config->set('HTML.TargetNoreferrer', false);
            $config->set('Cache.DefinitionImpl', null);

            $this->purifier = new HTMLPurifier($config);
        }

        return $this->purifier;
    }
}
