<footer class="app-footer">
    <div class="app-footer-inner">
        <div>
            <strong>{{ config('app.name', 'Smart Pharmacy') }}</strong>
            <span>&copy; {{ date('Y') }} All rights reserved.</span>
        </div>

        <div class="app-footer-links">
            <span>Secure</span>
            <span>Cloud Ready</span>
            <span>Mobile Friendly</span>
        </div>
    </div>
</footer>

<style>
    .app-footer {
        margin-top: 24px;
        padding: 0 18px 18px;
    }

    .app-footer-inner {
        padding: 16px 18px;
        border-top: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        color: #64748b;
        font-size: 12.5px;
        font-weight: 700;
    }

    .app-footer-inner strong {
        display: block;
        color: #0f172a;
        font-size: 14px;
        font-weight: 900;
        margin-bottom: 3px;
    }

    .app-footer-links {
        display: flex;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
    }

    .app-footer-links span {
        color: #64748b;
        white-space: nowrap;
    }

    @media (max-width: 767.98px) {
        .app-footer {
            padding: 0 12px 14px;
            margin-top: 16px;
        }

        .app-footer-inner {
            flex-direction: column;
            align-items: flex-start;
            padding: 14px 4px;
        }

        .app-footer-links {
            gap: 10px;
        }
    }
</style>