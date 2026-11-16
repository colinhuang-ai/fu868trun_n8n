# PageView.astro — luồng render

`src/components/PageView.astro` là phần thân của một trang CMS (collection `pages`), nằm bên trong layout `Base` do `PageShell` cung cấp.

## 1. Vị trí trong chuỗi render

```mermaid
flowchart TD
    R["Route<br/>index.astro / [slug].astro<br/>(EN và vi/)"] --> L["loadPage()<br/>src/utils/load-page.ts"]
    L -->|"null"| N["Astro.rewrite('/404')"]
    L -->|"{ page, title, description,<br/>canonical, robots, alternates }"| S["PageShell.astro"]
    S --> B["Base.astro<br/>header · footer · SEO · EN|VI"]
    B --> P["PageView.astro<br/>(slot: prop page)"]
```

## 2. Logic bên trong PageView

```mermaid
flowchart TD
    A["PageShell truyền prop page"] --> B{"blocks.length > 0 ?<br/>blocks = page.data.content ?? []"}

    B -- "có" --> C{"Có block<br/>_type = cases_hero ?"}
    B -- "không" --> E["Trường hợp 3: trang rỗng<br/>h1 = page.data.title"]

    C -- "có" --> D1["Trường hợp 1<br/>chỉ render block<br/>(hero tự mang h1)"]
    C -- "không" --> D2["Trường hợp 2<br/>header + h1 từ page.data.title<br/>rồi render block"]

    D1 --> CB["CasesBlocks<br/>_type → component trong blocks/"]
    D2 --> CB

    E --> U{"Astro.locals.user<br/>đã đăng nhập admin?"}
    U -- "có" --> ED["Hiện gợi ý t('page.empty')<br/>+ nút t('page.edit')<br/>→ /_emdash/admin/content/pages/ULID"]
    U -- "không" --> ONLY["Chỉ hiện tiêu đề"]

    classDef decision fill:#EEEDFE,stroke:#534AB7,color:#3C3489
    classDef result fill:#E1F5EE,stroke:#0F6E56,color:#085041
    classDef neutral fill:#F1EFE8,stroke:#5F5E5A,color:#444441
    class B,C,U decision
    class D1,D2,E,ED,ONLY result
    class A,CB neutral
```

## 3. Bảng tóm tắt

| Trường hợp | Điều kiện | Kết quả |
| --- | --- | --- |
| 1 | Có block và có `cases_hero` | Chỉ render block (hero tự mang `<h1>`) |
| 2 | Có block, không có hero | `<header><h1>` từ `page.data.title`, rồi render block |
| 3 | Không có block | `<h1>` tiêu đề; nếu đã đăng nhập thêm gợi ý và nút sửa trang |

## 4. Ghi chú

- Chỉ có một `<h1>` trên mỗi trang: biến `hasHero` ngăn việc in thêm `<h1>` khi hero đã có.
- `{...page.edit.title}` và `{...page.edit.content}` là các thuộc tính `data-*` của EmDash cho chỉnh sửa trực quan; ngoài chế độ edit chúng không sinh gì.
- Trường hợp 3 không phải 404: trang vẫn tồn tại, chỉ là chưa có block.
- `page.data.id` (ULID trong DB) mới là thứ URL admin cần, khác với `page.id` (slug, có tiền tố `vi/` ở bản tiếng Việt).
