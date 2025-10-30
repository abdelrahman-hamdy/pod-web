from playwright.sync_api import Page, expect

def verify_internships_page(page: Page):
    """
    This script verifies the new internships page.
    """
    # 1. Navigate to the internships page.
    page.goto("http://localhost:5173/internships")

    # 2. Take a screenshot of the "Available Internships" tab.
    expect(page.get_by_text("Available Internships")).to_be_visible()
    page.screenshot(path="jules-scratch/verification/available-internships.png")

    # 3. Click on the "My Applications" tab.
    my_applications_tab = page.get_by_role("button", name="My Applications")
    my_applications_tab.click()

    # 4. Take a screenshot of the "My Applications" tab.
    expect(page.get_by_text("My Applications")).to_be_visible()
    page.screenshot(path="jules-scratch/verification/my-applications.png")
