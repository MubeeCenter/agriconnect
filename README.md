# AgriConnect

**USSD-first agricultural marketplace for Northern Nigeria**

Built for the Africa's Talking Telecommunications Hackathon — Kano, 2026.

## What It Does

Farmers dial `*384*0032#` on any phone (smart or dumb, with or without internet) to:
- Check real-time market prices for Maize, Cowpea, and Tomato
- Find verified agro-dealers in their region
- Request a callback (Voice API) or send an SMS inquiry
- Verify prices via SMS crowdsourcing

## Tech Stack

- **Backend:** PHP 8.1 + MySQL
- **APIs:** Africa's Talking USSD, SMS, Voice
- **Container:** Docker (for Marketplace submission)
- **Frontend:** Vanilla CSS + JS (no frameworks)

## Directory Structure

See `/docs` or contact MubeeTech.

## Environment Variables

| Variable | Description |
|----------|-------------|
| `AT_USERNAME` | Africa's Talking username (use `sandbox` for testing) |
| `AT_API_KEY` | Africa's Talking API key |
| `AT_SENDER_ID` | Alphanumeric sender ID (e.g. `AgriConnect`) |
| `DB_HOST` | MySQL host |
| `DB_NAME` | MySQL database name |
| `DB_USER` | MySQL username |
| `DB_PASS` | MySQL password |
| `APP_ENV` | `development` or `production` |
| `BASE_URL` | Public URL of the app |

## Deployment

### Local
```bash
docker-compose up -d
 AgriConnect

**USSD-first agricultural marketplace for Northern Nigeria**

Built for the Africa's Talking Telecommunications Hackathon — Kano, 2026.

## What It Does

Farmers dial `*384*0032#` on any phone (smart or dumb, with or without internet) to:
- Check real-time market prices for Maize, Cowpea, and Tomato
- Find verified agro-dealers in their region
- Request a callback (Voice API) or send an SMS inquiry
- Verify prices via SMS crowdsourcing

## Tech Stack

- **Backend:** PHP 8.1 + MySQL
- **APIs:** Africa's Talking USSD, SMS, Voice
- **Container:** Docker (for Marketplace submission)
- **Frontend:** Vanilla CSS + JS (no frameworks)

## Directory Structure

See `/docs` or contact MubeeTech.

## Environment Variables

| Variable | Description |
|----------|-------------|
| `AT_USERNAME` | Africa's Talking username (use `sandbox` for testing) |
| `AT_API_KEY` | Africa's Talking API key |
| `AT_SENDER_ID` | Alphanumeric sender ID (e.g. `AgriConnect`) |
| `DB_HOST` | MySQL host |
| `DB_NAME` | MySQL database name |
| `DB_USER` | MySQL username |
| `DB_PASS` | MySQL password |
| `APP_ENV` | `development` or `production` |
| `BASE_URL` | Public URL of the app |

## Deployment

### Local
```bash
docker-compose up -d
## License
MIT — MubeeTech © 2026