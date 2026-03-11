<template>
  <footer class="footer">
    <div class="footer-content">
      <p class="footer-copyright">
        &copy; {{ currentYear }} Pineda Law Office. All rights reserved.
      </p>
      <p class="footer-text">
        Document Management System
      </p>
    </div>
  </footer>
</template>

<script setup>
const currentYear = new Date().getFullYear();
</script>

<style scoped>
.footer {
  background: linear-gradient(135deg, #1a4972 0%, #0f2f4a 100%);
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  font-family: 'Segoe UI', sans-serif;
  flex-shrink: 0;
  padding: 12px 16px;
}

.footer-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 12px;
}

.footer-copyright {
  font-size: 12px;
  color: rgba(255, 255, 255, 0.45);
  margin: 0;
  white-space: nowrap;
}

.footer-text {
  font-size: 12px;
  color: rgba(255, 255, 255, 0.35);
  margin: 0;
  white-space: nowrap;
}

/* Tablet responsiveness */
@media (max-width: 768px) {
  .footer {
    padding: 10px 12px;
  }

  .footer-content {
    flex-direction: column;
    gap: 6px;
  }

  .footer-copyright,
  .footer-text {
    font-size: 11px;
  }
}

/* Mobile responsiveness */
@media (max-width: 480px) {
  .footer {
    padding: 8px 12px;
  }

  .footer-copyright,
  .footer-text {
    font-size: 10px;
    white-space: normal;
    text-align: center;
    width: 100%;
  }

  .footer-content {
    gap: 4px;
  }
}
</style>