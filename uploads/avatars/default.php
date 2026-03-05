<?php
// Génère un avatar SVG par défaut
header('Content-Type: image/svg+xml');
echo '<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 80 80">
  <circle cx="40" cy="40" r="40" fill="#C8973A"/>
  <circle cx="40" cy="32" r="14" fill="white" opacity="0.9"/>
  <ellipse cx="40" cy="65" rx="22" ry="18" fill="white" opacity="0.9"/>
</svg>';
