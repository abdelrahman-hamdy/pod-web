# Render Deployment Guide

This guide provides the exact steps to deploy your Laravel application to Render, which offers a robust free tier for both the web service and the database.

## Step 1: Push Changes to GitHub

Ensure all the latest changes, including the `render.yaml` file, are pushed to your `main` branch on GitHub.

```bash
git add render.yaml
git commit -m "feat: configure for Render deployment"
git push origin main
```

## Step 2: Create the "Blueprint" Service on Render

1.  **Sign up or log in** to your Render account. You can sign up with your GitHub account, which makes the process easier.
2.  From the dashboard, click **New +** and select **Blueprint**.
3.  **Connect your GitHub account** if you haven't already.
4.  Select your `abdelrahman-hamdy/pod-web` repository from the list.
5.  Render will automatically detect and parse your `render.yaml` file. It will show you the `pod-web` service and the `pod-db` database that will be created.
6.  Click **Apply**.

That's it. Render will now start the first deployment. It will:
- Provision the free PostgreSQL database.
- Run the `buildCommand` from your `render.yaml`, which installs dependencies, builds assets, generates a key, and creates the storage link.
- Start your application using the `startCommand`.
- The **Persistent Disk** will be automatically created and mounted at `/var/www/html/storage/app/public`, solving the image and file upload problem permanently.

## Step 3: Run Migrations Manually (First Time Only)

Render doesn't have a "pre-deploy" job like other platforms. The first time, you'll need to run the migrations manually after the app is live.

1.  Wait for the first deployment to finish. It might fail because the database isn't migrated yet. This is okay.
2.  Go to your `pod-web` service in the Render dashboard.
3.  Click on the **Shell** tab.
4.  Run the migration and seeding command:
    ```bash
    php artisan migrate:fresh --seed --force
    ```
5.  After the command finishes, go back to the service's page and click **Manual Deploy** > **Deploy latest commit** to restart the server with the migrated database.

Your application is now live and fully functional on Render. Any future pushes to your `main` branch will automatically trigger a new deployment.
