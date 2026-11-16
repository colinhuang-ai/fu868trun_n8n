# CASES website

Corporate site for CaMau Seafood Processing and Service JSC, built with [EmDash](https://github.com/emdash-cms/emdash) on Astro and Cloudflare (D1 + R2). English at `/`, Vietnamese at `/vi/`.

## Develop

```bash
pnpm install
pnpm dev
```

Open http://localhost:4321/_emdash/api/setup/dev-bypass?redirect=/ once to apply the seed and sign in as a dev admin, then use http://localhost:4321/_emdash/admin to edit content.

The seed is generated: edit `scripts/build-seed.mjs`, run `node scripts/build-seed.mjs`, and validate with `npx emdash seed seed/seed.json --validate`.

## Deploy

```bash
pnpm wrangler login
pnpm deploy
```

See `AGENTS.md` for the project structure, content model and rules.
