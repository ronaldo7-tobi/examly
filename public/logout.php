<?php
// Znisczenie sesji.
session_destroy();

// Przekierowanie.
header('Location: /examly/public/login');
exit;
