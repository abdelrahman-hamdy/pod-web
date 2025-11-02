# DigitalOcean App Platform Deployment Guide

This guide provides the exact steps to deploy your Laravel application to the DigitalOcean App Platform. This method is reliable and designed for Laravel.

## Step 1: Push Changes to GitHub

Ensure all the latest changes, including the `.do/app.yml` file, are pushed to your `main` branch on GitHub.

```bash
git add .
git commit -m "feat: configure for DigitalOcean App Platform"
git push origin main
```

## Step 2: Create the App on DigitalOcean

1.  **Log in** to your DigitalOcean account.
2.  Navigate to the **Apps** section from the left-hand menu and click **Create App**.
3.  Choose **GitHub** as the source for your code.
4.  **Authorize** DigitalOcean to access your GitHub account if you haven't already.
5.  Select your `abdelrahman-hamdy/pod-web` repository from the list.
6.  The App Platform will automatically detect the `.do/app.yml` file and pre-fill the configuration. **You do not need to change anything here.**
7.  Click **Next**.
8.  DigitalOcean will show you the resources it's about to create (the `pod-web-app` service and the `db` database).
9.  Click **Next**.
10. Review the final details and click **Create Resources**.

DigitalOcean will now start the first deployment. It will:
- Build the application based on your `build_command`.
- Run the `migrate` job.
- Launch the `pod-web-app` service.

You can monitor the build and deployment process in real-time from the DigitalOcean dashboard.

## Step 3: Configure Environment Variables

The application needs a few environment variables to run correctly.

1.  In your app's dashboard on DigitalOcean, go to the **Settings** tab.
2.  Select the `pod-web-app` component.
3.  Scroll down to the **Environment Variables** section and click **Edit**.
4.  Add the following variables:
    - `APP_KEY`: Generate this locally with `php artisan key:generate --show` and paste the result (including `base64:`).
    - `APP_URL`: The URL provided by the App Platform (e.g., `https://pod-web-asdfg.ondigitalocean.app`). You can find this on your app's main dashboard page.
    - **All your other `.env` variables** for mail, etc., should be added here.

    **Important**: The `DATABASE_URL` is automatically injected by DigitalOcean because we linked the service and the database in the `app.yml` file. You do **not** need to set it manually.

5.  Click **Save**. This will trigger a new deployment with the updated environment variables.

## Solving the Storage & Image Problem

DigitalOcean App Platform's filesystem is ephemeral, meaning files created at runtime (like user uploads) are deleted on every new deployment. The correct way to handle this is with a persistent storage solution.

1.  **Create a DigitalOcean Space:** A "Space" is an S3-compatible object storage bucket. Create one in the same region as your app (e.g., `nyc3`).
2.  **Generate Space Keys:** Create an Access Key and Secret Key for your Space.
3.  **Update Environment Variables:** Add the following environment variables to your app on the App Platform:
    - `FILESYSTEM_DISK=s3`
    - `AWS_ACCESS_KEY_ID` (Your Space's Access Key)
    - `AWS_SECRET_ACCESS_KEY` (Your Space's Secret Key)
    - `AWS_DEFAULT_REGION` (e.g., `nyc3`)
    - `AWS_BUCKET` (The name of your Space)
    - `AWS_ENDPOINT` (e.g., `https://nyc3.digitaloceanspaces.com`)
    - `AWS_USE_PATH_STYLE_ENDPOINT=false`
4.  **Update Filesystem Config:** Ensure `config/filesystems.php` is configured to use these variables for the `s3` disk. The default Laravel configuration usually works perfectly.

After setting these variables, all file uploads using `Storage::disk('public')` (if you configure the `public` disk to use `s3`) will automatically go to your DigitalOcean Space and will persist between deployments.
