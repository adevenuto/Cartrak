# Laravel Cloud deployment

Tracks taking Ignition Index live on [Laravel Cloud](https://cloud.laravel.com) with CI on
GitHub Actions and a protected `main`. Update the checkboxes as steps land.

## Status

| Phase | State |
|---|---|
| A. Repository changes (`live_with_cicd`) | 🟡 Code done — PR to `development` pending |
| B. Branch protection on `main` | ⬜ Not started — needs CI to have run once |
| C. Laravel Cloud setup | ⬜ Not started |
| D. First release (`development` → `main`) | ⬜ Not started |

## How releases work

```
feature branch ──PR──▶ development ──PR──▶ main ──push──▶ Laravel Cloud deploys
                  │                  │
                  └─ CI runs         └─ CI runs, required to pass
```

1. **Every PR into `development` or `main`** runs `.github/workflows/ci.yml`: the full
   project gate against a real MySQL service.
2. **`main` is protected.** Direct pushes are rejected; code only arrives via a PR whose
   `Run Test Suite & Build` check passed.
3. **Merging into `main` is the deploy.** Laravel Cloud's push-to-deploy watches `main`.
   There are no deploy hooks and no deployment steps in GitHub Actions.

## Decisions

| Area | Choice | Why |
|---|---|---|
| Host | Laravel Cloud | Managed PHP runtime, database, storage, queue and scheduler; native push-to-deploy |
| CI checks | Full `composer ci:check` | Pest, PHPStan L7, Pint, `vp check`, `vue-tsc` — identical to local |
| CI scope | PRs into `main` and `development` | Feature work is tested when it lands, not only at release |
| Database | Laravel MySQL | Matches local development; CI tests against MySQL too |
| Photos | Private object storage bucket, streamed through the app | Keeps the owner check on every request; no shareable links |
| Queue | Managed queue | Cloud's recommended option; scales to zero; failed-jobs dashboard |
| Mail | Resend | First-party Laravel driver, one API key |
| Domain | Custom domain | `TODO: domain name` |
| Environments | Production only, on `main` | Preview environments can be added later |
| PHP | 8.3 | Matches `composer.json` (`^8.3`) and CI. Cloud defaults new environments to 8.5 — set it explicitly |
| Node | 22 (`.nvmrc`) | vite-plus supports `^20.19 \|\| ^22.18 \|\| >=24.11` |

## Phase A — Repository changes

Branch `live_with_cicd`, off `development`.

- [x] Replace the Hostinger-era `.github/workflows/tests.yml` with `.github/workflows/ci.yml`
- [x] Pin Node 22 in `.nvmrc`
- [x] `composer require league/flysystem-aws-s3-v3 aws/aws-sdk-php resend/resend-php`
- [x] Serve photos from any disk: `VehiclePhotoController` streams from the disk instead of
      `response()->file($disk->path())`, which only works on a local disk
- [x] `->onOneServer()` on both scheduled tasks
- [x] Production hints in `.env.example` (comments only — CI copies this file)
- [x] Suite verified against MySQL locally: all migrations run, 189/189 tests pass, and the
      tests were confirmed to hit MySQL rather than `phpunit.xml`'s sqlite
- [x] Laravel Cloud's build command verified on a fresh clone: `composer install --no-dev`,
      `npm ci`, `npm run build` and `php artisan optimize` all succeed; dev packages are
      stripped, config and routes cache (including the closure route), and the app boots
      as `production` with debug off
- [ ] Open PR `live_with_cicd` → `development`; **CI runs for the first time**
- [ ] Merge once green

## Phase B — Branch protection on `main`

Do this **after CI has run at least once**, so the check name is selectable. The GitHub
CLI isn't installed, so use the web UI.

GitHub → **Settings → Rules → Rulesets → New branch ruleset**:

- [ ] Name `Protect main`, enforcement **Active**, target branch `main`
- [ ] **Require a pull request before merging** (0 approvals — solo repository)
- [ ] **Require status checks to pass** → add `Run Test Suite & Build`; require branches to
      be up to date
- [ ] **Block force pushes**
- [ ] **Restrict deletions**
- [ ] **Leave the bypass list empty.** Otherwise the repository owner can still push to
      `main` directly, which defeats the point.
- [ ] Verify: a direct `git push origin main` is rejected

> The job's `name:` in `ci.yml` *is* the required check. Renaming the job silently detaches
> the rule — change both together.

## Phase C — Laravel Cloud setup

- [ ] **Application:** New application → GitHub `adevenuto/IgnitionIndex` → environment
      `production` on branch `main`, region closest to users, **push-to-deploy on**
- [ ] **PHP version:** General settings → **8.3**
- [ ] **App compute:** Flex size; **Scheduler** toggle **on** (Cloud then runs
      `schedule:run` every minute). Scale-to-zero is optional — Cloud wakes the environment
      for scheduled tasks and queued jobs
- [ ] **Database:** Add database → new **Laravel MySQL** cluster (Flex, 5 GB), database
      `ignitionindex`. Backups: daily, 7-day retention. **Note the MySQL version** and align
      `mysql:8.0` in `ci.yml` if it differs
- [ ] **Object storage:** Add bucket → Laravel Object Storage, visibility **Private**, disk
      name `photos`, **set as default disk**
- [ ] **Managed queue:** Add compute → Managed queue, Flex, 256 MiB, default queue
- [ ] **Build commands:**
      ```
      composer install --no-dev --optimize-autoloader && npm ci && npm run build && php artisan optimize
      ```
- [ ] **Deploy commands:**
      ```
      php artisan migrate --force
      ```
      Don't add `queue:restart`, `storage:link` or `optimize:clear` — Cloud restarts workers
      itself, and neither of the others belongs in a deploy on Cloud.
- [ ] **Environment variables:** see the table below
- [ ] **Resend:** add and verify the sending domain (DNS), create a production API key
- [ ] **Custom domain:** add it in the environment, apply the DNS records, wait for SSL

### Production environment variables

Cloud injects the database, object storage and queue settings when those resources are
attached. Set the rest manually.

| Variable | Value | Source |
|---|---|---|
| `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | — | Injected (database) |
| `FILESYSTEM_DISK`, bucket credentials | `photos` | Injected (object storage) |
| `QUEUE_CONNECTION` | `cloud` | Injected (managed queue) |
| `APP_NAME` | `Ignition Index` | Manual |
| `APP_ENV` | `production` | Manual |
| `APP_DEBUG` | `false` | Manual |
| `APP_KEY` | output of `php artisan key:generate --show` | Manual — keep secret |
| `APP_URL` | `https://TODO-domain` | Manual |
| `LOG_LEVEL` | `warning` | Manual |
| `APP_MAINTENANCE_DRIVER` | `cache` | Manual |
| `APP_MAINTENANCE_STORE` | `database` | Manual |
| `SESSION_DRIVER` | `database` | Manual |
| `CACHE_STORE` | `database` | Manual |
| `MAIL_MAILER` | `resend` | Manual |
| `RESEND_API_KEY` | from Resend | Manual — keep secret |
| `MAIL_FROM_ADDRESS` | e.g. `hello@TODO-domain` | Manual |
| `MAIL_FROM_NAME` | `Ignition Index` | Manual |

Only if the database connection is refused for lack of TLS:
`MYSQL_ATTR_SSL_CA=/etc/ssl/certs/ca-certificates.crt`.

## Phase D — First release

- [ ] Open PR `development` → `main`. It carries the whole rebrand, so review it rather
      than rubber-stamping it
- [ ] CI passes → merge
- [ ] Laravel Cloud deploys automatically — watch the build and deploy logs

## Go-live verification

- [ ] **CI enforces:** a deliberately broken test on a throwaway branch shows red on its PR
      and merging is blocked
- [ ] **Protection:** a direct push to `main` is rejected
- [ ] **Deploy:** merging to `main` starts a deployment with no manual step
- [ ] **Assets:** pages load with styles and fonts; no 404s for `/build/*`
- [ ] **Auth:** register → verification email arrives via Resend → the link verifies. An
      **"Invalid signature" 403** here means Cloud's proxy isn't trusted — add
      `$middleware->trustProxies(at: '*')` in `bootstrap/app.php`
- [ ] **Photos:** upload a photo, **redeploy**, confirm it still loads — proves it lives in
      the bucket, not on the ephemeral filesystem
- [ ] **Queue:** from the Commands tab, `php artisan reminders:send --force`; the job shows
      in the managed queue dashboard and the email arrives
- [ ] **Scheduler:** `php artisan schedule:list` shows `reminders:send` and `recalls:check`

## Things to know

- **The filesystem on Cloud is ephemeral and per-replica.** Anything written to local disk
  disappears on the next deploy. Persistent files belong in object storage.
- **CI tests on MySQL; local tests on sqlite.** CI sets `DB_*` as real environment
  variables, which take precedence over `phpunit.xml`'s sqlite `<env>` entries (PHPUnit
  only applies an `<env>` when the variable is unset and `force="true"` is absent).
  `composer ci:check` locally keeps using in-memory sqlite.
- **Decimal columns** (`gallons`, `mpg`, `avg_miles_per_day`) have their precision enforced
  by MySQL but not sqlite. A test that passes locally can fail in CI on an out-of-range
  value.
- **Reminders run at 08:00 UTC** (~4am US Eastern), because the app timezone is UTC.
  Worth revisiting before real users arrive.
- **`schedule:list` needs the database.** Cloud runs it at deploy time to work out when to
  wake a sleeping environment. Because both tasks use `withoutOverlapping()` and
  `onOneServer()`, it probes the lock in the `cache_locks` table — so it errors on a
  machine without a database. That is harmless: the probe is `Lock::get()` with a
  callback, which acquires and then releases in a `finally`, so listing the schedule can
  never hold a lock that blocks a real run.

## Follow-ups

- [ ] Fill in the custom domain everywhere marked `TODO`
- [ ] Confirm Cloud's MySQL version against the CI service image
- [ ] Decide whether trusted proxies are needed (the go-live auth check answers this)
- [ ] Consider a user-local reminder time
