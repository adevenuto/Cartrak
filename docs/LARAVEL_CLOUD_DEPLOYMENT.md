# Laravel Cloud deployment

Tracks taking Ignition Index live on [Laravel Cloud](https://cloud.laravel.com) with CI on
GitHub Actions and a protected `main`. Update the checkboxes as steps land.

## Status

| Phase | State |
|---|---|
| A. Repository changes (`live_with_cicd`) | ✅ Done — merged into `main` (#6) and `development` (#7), CI green on both |
| B. Branch protection on `main` | ✅ Done — ruleset active, direct push verified rejected |
| C. Laravel Cloud setup | ⬜ Not started |
| D. First release (`development` → `main`) | 🟡 Code already on `main` via #6 — no deploy yet, Cloud not connected |

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
- [x] Open PR `live_with_cicd` → `development`; **CI runs for the first time** — green
- [x] Merge once green

> **What actually happened.** Two PRs were open from `live_with_cicd`: #6 into `main` and #7
> into `development`. #6 was merged first, by accident. Because the branch was cut from
> `development`, that carried the whole rebrand plus CI into `main` — early, but only after
> CI had passed on that exact code. #7 was then merged, so `main` and `development` hold
> identical files. Nothing deployed, since Laravel Cloud was not yet connected.
>
> The two branches now differ only by PR merge commits with no file changes. That is normal
> with GitHub's "Create a merge commit" setting and will recur on every release.
>
> **Lesson:** GitHub's "Compare & pull request" banner defaults the base to `main`. Check the
> base branch before creating a PR from a feature branch.

## Phase B — Branch protection on `main`

Do this **after CI has run at least once**, so the check name is selectable. The GitHub
CLI isn't installed, so use the web UI.

GitHub → **Settings → Rules → Rulesets → New branch ruleset**:

- [x] Name `Protect Main` (ruleset id `23576332`), enforcement **Active**, target
      `refs/heads/main`
- [x] **Require a pull request before merging** (0 approvals — solo repository)
- [x] **Require status checks to pass** → `Run Test Suite & Build`; require branches to be
      up to date
- [x] **Block force pushes**
- [x] **Restrict deletions**
- [x] **Leave the bypass list empty.** Otherwise the repository owner can still push to
      `main` directly, which defeats the point.
- [x] Verify: a direct push to `main` is rejected

**How it was verified.** GitHub's rule evaluation for `main`
(`GET /repos/adevenuto/IgnitionIndex/rules/branches/main`, public, no auth) returns
`deletion`, `non_fast_forward`, `pull_request` (0 approvals) and `required_status_checks`
(`Run Test Suite & Build`, strict). The bypass list is only visible to admins, so it was
proven with a real push instead: an empty commit built on `main` and pushed by the
repository owner was refused.

```
remote: error: GH013: Repository rule violations found for refs/heads/main.
- Changes must be made through a pull request.
- Required status check "Run Test Suite & Build" is expected.
! [remote rejected] … -> main (push declined due to repository rule violations)
```

Because it was the owner's own push that was declined, nobody is on the bypass list.

> **In zsh, brace the variable in a refspec** — `"${sha}:refs/heads/main"`, not
> `"$sha:refs/heads/main"`. zsh reads `$sha:r` as its `:r` modifier and mangles the refspec,
> so git fails locally without ever contacting GitHub. The first attempt at this test did
> exactly that, and it looked like a pass.

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

- [x] ~~Open PR `development` → `main`~~ — superseded: the rebrand and CI reached `main`
      through #6 (see Phase A). CI passed on that code before the merge
- [x] CI passes → merge
- [ ] **Connect Laravel Cloud to `main` (Phase C).** Because `main` already holds the
      release, the first deployment happens as soon as Cloud is connected — there is no
      separate merge to trigger it. Finish the environment variables *before* the first
      deploy, or it boots without an `APP_KEY` or mail settings
- [ ] Laravel Cloud deploys — watch the build and deploy logs

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
