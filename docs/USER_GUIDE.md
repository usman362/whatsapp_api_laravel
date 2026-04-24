# WhatsApp Backend — User Guide (Admin)

This guide explains how to configure the WhatsApp backend, add users, connect the Meta WhatsApp Cloud API webhook, and test the full check-in / check-out flow.

## 1) Admin Login

- Open: `http://127.0.0.1:8000/login` (local) or your production domain `/login`
- Default admin (dev):
  - Email: `admin@timhr.es`
  - Password: `password`

If the admin user is missing, run:

```bash
php artisan migrate --seed
```

## 2) Configure Settings (Required)

Go to **Admin → Settings** and set:

- **WhatsApp Access Token**
  - Meta Cloud API access token
  - Stored encrypted in the database
- **Phone Number ID**
  - The Meta phone number ID
- **Verify Token**
  - Any secret string you choose
  - Must match the token used when verifying the webhook in Meta
  - Stored encrypted in the database
- **Graph Version**
  - Example: `v18.0`
- **Company API Timeout**
  - Default: `10` seconds

Click **Save Settings**.

## 3) Add WhatsApp Users (Per Employee)

Go to **Admin → WA Users** → **Create User**:

- **Phone (E.164)**: e.g. `+34123456789`
- **API Base URL**: e.g. `https://company.timhr.es/api`
- **API Token**: optional (if company API requires authentication)
- **Active**: enabled

Each user can have a different `api_base_url` (multi-company).

## 4) Company API Endpoints (Expected)

For each user’s `api_base_url`, the backend will call:

- `GET {api_base_url}/last_status`
- `POST {api_base_url}/check_in`
- `POST {api_base_url}/check_out`
- `GET {api_base_url}/worked_time`

Payload example (for `check_in` / `check_out`):

```json
{ "phone": "+34123456789" }
```

Worked time response example:

```json
{ "hours": 8, "minutes": 53 }
```

## 5) Webhook URL (Meta WhatsApp Cloud API)

The project exposes:

- `GET /api/webhooks/whatsapp` — webhook verification (challenge)
- `POST /api/webhooks/whatsapp` — incoming messages + interactive button replies

### Local development note

Meta requires a **public HTTPS** webhook URL. For local testing, use a tunnel (ngrok / Cloudflare Tunnel) to expose:

- Local app: `http://127.0.0.1:8000`
- Public webhook URL becomes:
  - `https://YOUR-TUNNEL-DOMAIN/api/webhooks/whatsapp`

### Production

Use your production domain:

- `https://YOUR-DOMAIN/api/webhooks/whatsapp`

## 6) Configure the Webhook in Meta

In Meta’s WhatsApp Cloud API dashboard:

1. Go to **Webhooks**
2. Set:
   - **Callback URL**: `https://YOUR-DOMAIN/api/webhooks/whatsapp`
   - **Verify Token**: the same value you saved in **Admin → Settings**
3. Click **Verify and Save**
4. Subscribe to **messages** events as required.

## 7) WhatsApp Conversation Flow (What the user sees)

When a registered user sends any message (e.g. “hola”, “hi”, “check”), the backend:

1. Identifies the user by phone number (E.164).
2. Calls the company API `last_status`.
3. Replies with buttons:
   - If status is **OUT** → show **Entrar**
   - If status is **IN** → show **Salir**
4. Also provides a **Tiempo trabajado** button to display worked time.

### Button actions

- **Entrar**:
  - Calls `check_in`
  - Replies with confirmation and shows **Salir**
- **Salir**:
  - Calls `check_out`, then `worked_time`
  - Replies with “Hoy has trabajado: Xh Ym”
- **Tiempo trabajado**:
  - Calls `worked_time`
  - Replies with “Hoy has trabajado: Xh Ym”

## 8) Duplicate Webhook Protection (Idempotency)

WhatsApp can resend the same webhook event. The backend stores each provider `message_id` in `wa_processed_messages` and skips duplicates to prevent double check-in/out.

## 9) Troubleshooting

- **Webhook verification fails (403)**:
  - Check **Verify Token** in Admin Settings matches the one in Meta
- **No messages received**:
  - Confirm Meta is pointing to the correct public HTTPS callback URL
  - Confirm you subscribed to **messages**
- **Buttons not working**:
  - Ensure the webhook is receiving **interactive** message events
- **Company API errors**:
  - The user will receive: “No he podido fichar ahora. Inténtalo más tarde.”
  - Check logs in `storage/logs/whatsapp.log`
- **User says “not registered”**:
  - Add the phone to **WA Users** in E.164 format (with `+`)

## 10) Security Notes

- Keep WhatsApp tokens private.
- Rotate tokens if leaked.
- Limit admin access and use strong passwords in production.

