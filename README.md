# Bissell B2C Service CI/CD Pipeline

Automated CI/CD pipeline for managing Oracle B2C Service customisations across all Bissell environments and interfaces using GitHub Actions.

---

## Environments

| Environment | URL | Purpose |
|---|---|---|
| TST2 | bissell--tst2.custhelp.com | Development |
| TST1 | bissell--tst1.custhelp.com | Testing |
| TST3 | bissell--tst3.custhelp.com | Training |
| PROD | support.bissell.com | Production |

---

## Interfaces

| Interface | Description |
|---|---|
| bissell | Bissell Main |
| bissell_au | Bissell Australia |
| bissell_de | Bissell Germany |
| bissell_es | Bissell Spain |
| bissell_fr | Bissell France |
| bissell_it | Bissell Italy |
| bissell_nl | Bissell Netherlands |
| bissell_pl | Bissell Poland |
| bissell_pt | Bissell Portugal |
| bissell_se | Bissell Sweden |
| bissell_uk | Bissell United Kingdom |
| interface_12 | Placeholder - TBD |

---

## Branch Strategy

| Branch | Environment | Purpose |
|---|---|---|
| `tst2` | TST2 | Default development branch |
| `tst1` | TST1 | Test environment branch |
| `tst3` | TST3 | Training environment branch |
| `main` | PROD | Production branch |
| `story-ADO-{number}` | N/A | Feature branch per Azure DevOps story |
| `DIFF_BRANCH` | N/A | Base branch used for diff comparisons |

---

## Repository Structure

```
bissell-b2c-cicd-test/
├── .github/
│   └── workflows/
│       ├── checkin-pipeline.yml          # Check-in non-coded elements to Git
│       ├── coded-elements-pipeline.yml   # CI/CD for coded elements
│       ├── story-deployment-pipeline.yml # Deploy stories between environments
│       └── adhoc-deployment-pipeline.yml # Ad-hoc element migration
├── customisations/
│   ├── bissell/
│   │   ├── extensions/
│   │   ├── scripts/
│   │   ├── cp_files/
│   │   ├── process_models/
│   │   ├── workspaces/
│   │   ├── reports/
│   │   ├── navigation_sets/
│   │   ├── agent_scripts/
│   │   ├── standard_text/
│   │   └── ebr/
│   └── [one folder per interface, same structure]
├── elementManagerZips/
│   ├── PROD/
│   ├── TST1/
│   ├── TST2/
│   └── TST3/
├── storyReferences/
├── deploymentConfig.json
├── elementsConfig.json
└── README.md
```

---

## Pipelines

### 1. Check-In Pipeline
**File:** `.github/workflows/checkin-pipeline.yml`
**Trigger:** Manual
**Purpose:** Exports non-coded elements (Workspaces, Reports, etc.) from a B2C environment and checks them into the corresponding Git story branch.

**Inputs:**
- Source environment (TST2, TST1, TST3)
- Interface name
- Story number (ADO-XXXX)
- Element type
- Element name or ID

---

### 2. Coded Elements Pipeline
**File:** `.github/workflows/coded-elements-pipeline.yml`
**Trigger:** Automatic on push to a story branch
**Purpose:** Runs linting, unit tests, and static code analysis on coded elements before deploying to TST2 for developer testing.

**Stages:**
1. Code Linting (ESLint)
2. Unit Testing
3. Static Code Analysis
4. Deploy to TST2
5. Automated Testing
6. Rollback (if tests fail)

---

### 3. Story Deployment Pipeline
**File:** `.github/workflows/story-deployment-pipeline.yml`
**Trigger:** Manual
**Purpose:** Deploys one or more completed stories from a source environment to a target environment. Includes a diff review step before deployment proceeds.

**Inputs:**
- Source environment
- Target environment
- Story number(s) (optional — uses deploymentConfig.json if blank)

**Stages:**
1. Export from source branch
2. Export from target branch
3. View diff between environments
4. Manual approval to proceed
5. Deploy to target environment
6. Update target branch in Git
7. Automated testing
8. Rollback (if tests fail)

---

### 4. Ad-hoc Deployment Pipeline
**File:** `.github/workflows/adhoc-deployment-pipeline.yml`
**Trigger:** Manual
**Purpose:** One-off migration of specific elements between environments or interfaces. Supports two modes:
- **SITE-SITE** — Migrate elements from one environment to another
- **INTERFACE-INTERFACES** — Migrate elements from one interface to other interfaces within the same environment

**Inputs:**
- Build mode (SITE-SITE or INTERFACE-INTERFACES)
- Source environment and interface
- Target environment and interface(s)
- Element type and name/ID

---

## Getting Started

### Prerequisites
- Access to GitHub repository
- Azure DevOps story number for your work
- Access to relevant B2C Service environments

### Making a Change (Non-Coded Element Example)

1. Make your change in **TST2** as normal
2. Go to **Actions** tab in GitHub
3. Select **Check-In Pipeline**
4. Click **Run Workflow**
5. Fill in the form with your ADO story number, interface, and element details
6. Click **Run**
7. Verify the element appears in your story branch
8. Open a **Pull Request** when your story is complete
9. Once approved and merged, use the **Story Deployment Pipeline** to promote to TST1
10. After testing is complete, use the **Story Deployment Pipeline** to promote to PROD

### Making a Change (Coded Element Example)

1. Create a local story branch:
   ```
   git checkout tst2
   git pull origin tst2
   git checkout -b story-ADO-XXXX
   ```
2. Make your code changes in VS Code
3. Push your branch:
   ```
   git add .
   git commit -m "ADO-XXXX: Description of change"
   git push origin story-ADO-XXXX
   ```
4. The **Coded Elements Pipeline** triggers automatically
5. Review pipeline results in the **Actions** tab
6. Open a **Pull Request** when ready for review
7. Once approved, use the **Story Deployment Pipeline** to promote

---

## Secrets Configuration

The following secrets must be configured in GitHub under **Settings → Environments** for each environment:

| Secret Name | Purpose |
|---|---|
| `B2C_PRIVATE_KEY` | SSO private key for Element Manager API |
| `B2C_SITE_LOGIN_USER` | Service account username for EM API |
| `B2C_SITE_LOGIN_PASS` | Service account password for EM API |
| `B2C_CP_LOGIN_USER` | Customer Portal Basic Auth username |
| `B2C_CP_LOGIN_PASS` | Customer Portal Basic Auth password |

---

## Support

For questions or issues with this pipeline, contact the B2C Service admin or raise an Azure DevOps story for pipeline-related work.