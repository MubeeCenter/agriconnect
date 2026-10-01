# AgriConnect — Marketplace Submission

## Basic Info

**Name:** AgriConnect
**Slug:** agriconnect
**Tagline:** USSD-first market prices and agro-dealer network

**Short Description (100 chars):**
Live crop prices and verified agro-dealers via USSD. No internet required.

**Long Description:**
AgriConnect is a USSD-first agricultural marketplace that connects smallholder farmers across Northern Nigeria to real-time market prices and verified agro-dealers. It requires no internet, no smartphone, and no app — just a basic phone and the shortcode.

**How it works:**
1. Farmers dial *384*0032# from any phone
2. Choose to check market prices for maize, cowpea, or tomato across Kano, Kaduna, or Abuja
3. Prices are crowdsourced via SMS — 3+ independent verifications earn a "Verified" badge
4. Find verified agro-dealers nearby
5. Press "1" to request a direct voice callback from the dealer via Africa's Talking Voice API

**Why this matters:**
Millions of farmers sell their harvest blind, losing up to 40% of potential revenue to middlemen who control price information. AgriConnect democratizes access to this data through the one interface that works everywhere in rural Africa: USSD.

**Powered by Africa's Talking APIs:**
- **USSD** — the entire farmer interface
- **SMS** — crowdsourced price verification and alerts
- **Voice** — direct farmer-to-dealer callbacks

**Configurable per deployment:**
- AT username & API key
- Sender ID
- USSD shortcode
- Database connection
- Site name & contact email

**Reusable by any business:** Each deployment is fully isolated with its own database and configuration. Ideal for:
- Agricultural cooperatives
- Farm input suppliers
- Commodity buyers
- NGOs and extension services
- State agriculture ministries

## Categorization

**AT Products used:** USSD, SMS, Voice
**Industry:** Agriculture (Other)

## Pricing Plans

**Plan 1: Free**
- Currency: NGN
- Amount: 0
- Frequency: Monthly
- Description: Free for the first 100 farmers. Includes full USSD and SMS access. Voice callbacks billed at cost.

**Plan 2: Standard**
- Currency: NGN
- Amount: 15000
- Frequency: Monthly
- Description: Unlimited farmers. Includes 500 SMS and 100 voice callbacks per month.

**Plan 3: Enterprise**
- Currency: NGN
- Amount: 75000
- Frequency: Monthly
- Description: Unlimited farmers, unlimited SMS, unlimited voice callbacks, priority support, custom branding.

## Environment Variables (declared at deploy time)

| Variable | Required | Default | Description |
|----------|----------|---------|-------------|
| AT_USERNAME | Yes | — | Africa's Talking username |
| AT_API_KEY | Yes | — | Africa's Talking API key |
| AT_SENDER_ID | No | AgriConnect | SMS Sender ID |
| AT_USSD_CODE | No | *384*0032# | USSD shortcode |
| DB_HOST | Yes | localhost | MySQL host |
| DB_NAME | Yes | agriconnect | MySQL database name |
| DB_USER | Yes | root | MySQL user |
| DB_PASS | Yes | — | MySQL password |
| BASE_URL | Yes | — | Public URL of the deployment |
| APP_ENV | No | production | Environment |
| MAIL_ADMIN_EMAIL | No | — | Admin notification email |

## Database

- **Required:** MySQL 8.0+
- **Auto-provisioned:** All tables (`agri_*`) are created on first USSD hit via `CREATE TABLE IF NOT EXISTS`
- **Seed data:** Includes sample listings and dealers for immediate testing

## Logo

File: `assets/images/logo/agriconnect-logo.png` (512x512 PNG)
Mark: "AC" monogram on green gradient

## Container

- **Image:** `registry.africastalking.marketplace.dev/agriconnect:latest`
- **Port:** 80
- **Health check:** `GET /`
- **Base image:** `php:8.1-apache`

## Terms Accepted

- [x] Africa's Talking Marketplace Terms of Service
- [x] Africa's Talking Privacy Policy