import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import postcss from 'postcss';

function academyCriticalStyles() {
    const criticalClasses = /\.(hero-banner|banner-[\w-]+|site-header|wordmark[\w-]*|main-nav|header-cta|menu-toggle|wrap|button|text-link|eyebrow|control-icon|icon-inline|skip-link|reading-progress|floating-[\w-]+|back-to-top|marquee[\w-]*|stats-strip)\b/;
    const criticalElement = /^(\*|:root|::selection|:focus-visible|\[hidden\]|html\b|body\b|a\b|button\b|img\b|h1\b|h2\b|h3\b|p\b|em\b|figure\b)/;

    return {
        name: 'academy-critical-styles',
        apply: 'build',
        enforce: 'post',
        generateBundle(_, bundle) {
            const stylesheet = Object.values(bundle).find(asset => asset.type === 'asset' && /academy-public-.*\.css$/.test(asset.fileName));
            if (!stylesheet) throw new Error('The academy stylesheet is missing.');
            const root = postcss.parse(String(stylesheet.source));
            const prune = container => {
                container.each(node => {
                    if (node.type === 'rule') {
                        const selectors = node.selectors.filter(selector => criticalElement.test(selector) || criticalClasses.test(selector));
                        if (!selectors.length) node.remove();
                        else node.selectors = selectors;
                    } else if (node.type === 'atrule') {
                        if (node.name === 'font-face' || (node.name.endsWith('keyframes') && ['type-marquee', 'scroll-cue'].includes(node.params))) return;
                        if (node.nodes && ['media', 'supports', 'layer'].includes(node.name)) {
                            prune(node);
                            if (!node.nodes.length) node.remove();
                        } else node.remove();
                    } else node.remove();
                });
            };
            prune(root);
            this.emitFile({type: 'asset', fileName: 'academy-critical.css', source: root.toString()});
        },
    };
}

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/academy-public.css', 'resources/css/workshop-public.css', 'resources/js/academy-public.js', 'resources/js/workshop-public.js'],
            buildDirectory: 'assets',
            refresh: true,
        }),
        tailwindcss(),
        academyCriticalStyles(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
