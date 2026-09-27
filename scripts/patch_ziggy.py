#!/usr/bin/env python3
"""Idempotent Ziggy wiring patches for the toggleable auth features branch."""

import re
from pathlib import Path

ROOT = Path("/home/z/my-project")


def write_if_changed(path: Path, content: str) -> bool:
    if path.read_text() == content:
        return False
    path.write_text(content)
    return True


APP_JS = """import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from 'ziggy-js';
import { createApp, h } from 'vue';
import '../css/app.css';

createInertiaApp({
    resolve: (name) =>
        resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: { color: '#4B5563' },
});
"""

SSR_JS = """import { createInertiaApp } from '@inertiajs/vue3';
import createServer from '@inertiajs/vue3/server';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from 'ziggy-js';
import { renderToString } from '@vue/server-renderer';
import { createSSRApp, h } from 'vue';
import '../css/app.css';

createServer((page) =>
    createInertiaApp({
        page,
        render: renderToString,
        resolve: (name) =>
            resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
        setup({ App, props, plugin }) {
            return createSSRApp({ render: () => h(App, props) })
                .use(plugin)
                .use(ZiggyVue, props.initialPage.props.ziggy);
        },
    }),
);
"""


def patch_middleware() -> None:
    path = ROOT / "app/Http/Middleware/HandleInertiaRequests.php"
    content = path.read_text()
    if "use Tightenco\\Ziggy\\Ziggy;" not in content:
        content = content.replace(
            "use Inertia\\Middleware;",
            "use Inertia\\Middleware;\nuse Tightenco\\Ziggy\\Ziggy;",
        )
    if "'ziggy' =>" not in content:
        content = content.replace(
            "            'status' => fn (): ?string => $request->session()->get('status'),",
            "            'ziggy' => fn (): array => (new Ziggy)->toArray(),\n"
            "            'status' => fn (): ?string => $request->session()->get('status'),",
        )
    path.write_text(content)


def add_use_route(relative: str) -> None:
    path = ROOT / relative
    content = path.read_text()
    if "useRoute" in content:
        return
    for import_line in [
        "import { useForm, usePage } from '@inertiajs/vue3';",
        "import { Link, useForm, usePage } from '@inertiajs/vue3';",
    ]:
        if import_line in content:
            content = content.replace(
                import_line,
                import_line + "\nimport { useRoute } from 'ziggy-js';",
                1,
            )
            break
    # Insert the route binding right after the imports block.
    content = re.sub(
        r"(import [^\n]+\n)(\n)",
        r"\1\nconst route = useRoute(usePage().props.ziggy);\n\2",
        content,
        count=1,
    )
    path.write_text(content)


def main() -> None:
    changed = []
    if write_if_changed(ROOT / "resources/js/app.js", APP_JS):
        changed.append("app.js")
    if write_if_changed(ROOT / "resources/js/ssr.js", SSR_JS):
        changed.append("ssr.js")
    patch_middleware()
    changed.append("middleware")
    for relative in [
        "resources/js/Pages/Profile/Edit.vue",
        "resources/js/Pages/Security/Index.vue",
        "resources/js/Pages/Teams/Index.vue",
        "resources/js/Pages/Teams/Show.vue",
    ]:
        add_use_route(relative)
        changed.append(relative)
    print("PATCHED:", ", ".join(changed))


if __name__ == "__main__":
    main()
