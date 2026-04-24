import os
from datetime import datetime


def _require_reportlab():
    try:
        import reportlab  # noqa: F401
    except Exception as e:
        raise SystemExit(
            "reportlab is required. Install with:\n"
            "  python3 -m venv .venv-docs\n"
            "  . .venv-docs/bin/activate\n"
            "  pip install reportlab\n"
        ) from e


def build_pdf(output_path: str) -> None:
    _require_reportlab()

    from reportlab.lib.pagesizes import A4
    from reportlab.lib.styles import getSampleStyleSheet
    from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer
    from reportlab.lib.units import cm

    styles = getSampleStyleSheet()
    title = styles["Title"]
    h2 = styles["Heading2"]
    body = styles["BodyText"]

    doc = SimpleDocTemplate(
        output_path,
        pagesize=A4,
        leftMargin=2 * cm,
        rightMargin=2 * cm,
        topMargin=2 * cm,
        bottomMargin=2 * cm,
        title="WhatsApp Backend — User Guide (Admin)",
        author="whatsapp.timhr.es",
    )

    story = []
    story.append(Paragraph("WhatsApp Backend — User Guide (Admin)", title))
    story.append(Spacer(1, 12))
    story.append(
        Paragraph(
            "This document explains how to configure the WhatsApp backend, add users, connect the Meta WhatsApp Cloud API webhook, and test the full check-in / check-out flow.",
            body,
        )
    )
    story.append(Spacer(1, 12))

    def section(name: str, paragraphs: list[str]):
        story.append(Paragraph(name, h2))
        story.append(Spacer(1, 6))
        for p in paragraphs:
            story.append(Paragraph(p, body))
            story.append(Spacer(1, 6))
        story.append(Spacer(1, 6))

    section(
        "1) Admin Login",
        [
            "Open <b>/login</b> on your local server or production domain.",
            "Default admin (dev): <b>admin@timhr.es</b> / <b>password</b>.",
            "If missing: run <b>php artisan migrate --seed</b>.",
        ],
    )

    section(
        "2) Configure Settings (Required)",
        [
            "Go to <b>Admin → Settings</b> and fill:",
            "- WhatsApp Access Token (saved encrypted)",
            "- Phone Number ID",
            "- Verify Token (saved encrypted; must match Meta webhook verification token)",
            "- Graph Version (e.g. v18.0)",
            "- Company API Timeout (default 10 seconds)",
            "Click <b>Save Settings</b>.",
        ],
    )

    section(
        "3) Add WhatsApp Users (Per Employee)",
        [
            "Go to <b>Admin → WA Users</b> → <b>Create User</b> and set:",
            "- Phone (E.164): e.g. +34123456789",
            "- API Base URL: e.g. https://company.timhr.es/api",
            "- API Token: optional (if company API requires auth)",
            "- Active: enabled",
            "Each user can have a different <b>api_base_url</b> (multi-company).",
        ],
    )

    section(
        "4) Company API Endpoints (Expected)",
        [
            "The backend calls these endpoints under each user’s api_base_url:",
            "- GET /last_status",
            "- POST /check_in",
            "- POST /check_out",
            "- GET /worked_time",
            "Payload for check_in/check_out: <b>{ \"phone\": \"+34...\" }</b>",
            "worked_time response: <b>{ \"hours\": 8, \"minutes\": 53 }</b>",
        ],
    )

    section(
        "5) Webhook URL (Meta WhatsApp Cloud API)",
        [
            "Routes:",
            "- GET /api/webhooks/whatsapp (verification)",
            "- POST /api/webhooks/whatsapp (messages + interactive replies)",
            "Local development requires a <b>public HTTPS</b> URL via a tunnel (ngrok / Cloudflare Tunnel).",
            "Production webhook URL example: <b>https://YOUR-DOMAIN/api/webhooks/whatsapp</b>",
        ],
    )

    section(
        "6) Configure the Webhook in Meta",
        [
            "In Meta WhatsApp Cloud API dashboard → Webhooks:",
            "1) Set Callback URL to your public HTTPS URL",
            "2) Set Verify Token to match Admin Settings",
            "3) Verify and Save",
            "4) Subscribe to messages events",
        ],
    )

    section(
        "7) WhatsApp Conversation Flow",
        [
            "User sends any message → backend finds the user by phone → calls last_status → shows buttons:",
            "- If OUT → Entrar",
            "- If IN → Salir",
            "There is also a <b>Tiempo trabajado</b> button to show worked time.",
            "Entrar calls check_in. Salir calls check_out then worked_time. Tiempo trabajado calls worked_time.",
        ],
    )

    section(
        "8) Duplicate Webhook Protection (Idempotency)",
        [
            "WhatsApp may resend the same webhook event. The backend stores provider message_id in wa_processed_messages and skips duplicates to prevent double check-in/out.",
        ],
    )

    section(
        "9) Troubleshooting",
        [
            "Webhook verify fails (403): verify token mismatch.",
            "No messages received: callback URL not public HTTPS or not subscribed to messages.",
            "Company API errors: check company endpoints/auth; review storage/logs/whatsapp.log.",
            "User not registered: ensure phone is added in WA Users in E.164 (+) format and active.",
        ],
    )

    section(
        "10) Security Notes",
        [
            "Keep tokens private and rotate if leaked. Restrict admin access and use strong passwords in production.",
        ],
    )

    story.append(Spacer(1, 18))
    story.append(
        Paragraph(
            f"Generated: {datetime.utcnow().strftime('%Y-%m-%d %H:%M UTC')}",
            styles["Italic"],
        )
    )

    doc.build(story)


if __name__ == "__main__":
    repo_root = os.path.abspath(os.path.join(os.path.dirname(__file__), ".."))
    out_dir = os.path.join(repo_root, "docs")
    os.makedirs(out_dir, exist_ok=True)
    out_path = os.path.join(out_dir, "WhatsApp_Backend_User_Guide.pdf")
    build_pdf(out_path)
    print(out_path)

