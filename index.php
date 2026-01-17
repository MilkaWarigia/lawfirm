<?php
/**
 * WELCOME DASHBOARD / LANDING PAGE
 * Main entry point - Welcome page for the Law Firm Management System
 */

session_start();

// If already logged in, redirect to appropriate dashboard
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role'])) {
    $role = $_SESSION['user_role'];
    if ($role === 'admin') {
        header("Location: admin/dashboard.php");
    } elseif ($role === 'advocate') {
        header("Location: advocate/dashboard.php");
    } elseif ($role === 'receptionist') {
        header("Location: receptionist/dashboard.php");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - Law Firm Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .top-navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px) saturate(180%);
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            padding: 15px 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .nav-content-wrapper {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .nav-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 20px;
            font-weight: 700;
            color: #1e293b;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .nav-brand:hover {
            color: #8b5cf6;
        }
        
        .nav-brand i {
            color: #8b5cf6;
            font-size: 24px;
        }
        
        .nav-links {
            display: flex;
            align-items: center;
            gap: 30px;
            list-style: none;
        }
        
        .nav-links a {
            color: #475569;
            text-decoration: none;
            font-weight: 500;
            font-size: 15px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 8px;
        }
        
        .nav-links a:hover {
            background: rgba(139, 92, 246, 0.1);
        }
        
        .nav-links a:hover {
            color: #8b5cf6;
        }
        
        .nav-links .btn-client-login {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            transition: all 0.3s ease;
        }
        
        .nav-links .btn-client-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.4);
            color: white;
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
        }
        
        .nav-links .btn-signin {
            background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%);
            color: white;
            padding: 10px 24px;
            border-radius: 10px;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
            transition: all 0.3s ease;
        }
        
        .nav-links .btn-signin:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(139, 92, 246, 0.4);
            color: white;
        }
        
        .welcome-page {
            min-height: 100vh;
            background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 50%, #ec4899 100%);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            position: relative;
            overflow-y: auto;
            overflow-x: hidden;
            padding-top: 70px;
        }
        
        .welcome-page::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 1px, transparent 1px);
            background-size: 60px 60px;
            animation: move 25s linear infinite;
            opacity: 0.4;
        }
        
        /* Floating particles */
        .floating-particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 0;
            overflow: hidden;
        }
        
        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(255, 255, 255, 0.6);
            border-radius: 50%;
            animation: floatParticle 15s infinite ease-in-out;
        }
        
        .particle:nth-child(1) { left: 10%; animation-delay: 0s; animation-duration: 12s; }
        .particle:nth-child(2) { left: 20%; animation-delay: 2s; animation-duration: 15s; }
        .particle:nth-child(3) { left: 30%; animation-delay: 4s; animation-duration: 18s; }
        .particle:nth-child(4) { left: 40%; animation-delay: 1s; animation-duration: 14s; }
        .particle:nth-child(5) { left: 50%; animation-delay: 3s; animation-duration: 16s; }
        .particle:nth-child(6) { left: 60%; animation-delay: 5s; animation-duration: 13s; }
        .particle:nth-child(7) { left: 70%; animation-delay: 2.5s; animation-duration: 17s; }
        .particle:nth-child(8) { left: 80%; animation-delay: 4.5s; animation-duration: 19s; }
        .particle:nth-child(9) { left: 90%; animation-delay: 1.5s; animation-duration: 11s; }
        
        @keyframes floatParticle {
            0% {
                transform: translateY(100vh) translateX(0) scale(0);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100px) translateX(50px) scale(1);
                opacity: 0;
            }
        }
        
        @keyframes move {
            from { transform: translate(0, 0); }
            to { transform: translate(60px, 60px); }
        }
        
        .welcome-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 20px;
            position: relative;
            z-index: 1;
        }
        
        .hero-section {
            text-align: center;
            padding: 40px 20px 60px;
            color: #ffffff;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }
        
        .hero-icon {
            width: 110px;
            height: 110px;
            margin: 0 auto 20px;
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(25px) saturate(180%);
            border-radius: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.3), 0 0 60px rgba(139, 92, 246, 0.3);
            animation: heroPulse 3s ease-in-out infinite, rotateGlow 8s linear infinite;
            position: relative;
        }
        
        .hero-icon::before {
            content: '';
            position: absolute;
            inset: -3px;
            border-radius: 28px;
            padding: 3px;
            background: linear-gradient(45deg, #8b5cf6, #6366f1, #ec4899, #8b5cf6);
            background-size: 300% 300%;
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            animation: gradientRotate 4s ease infinite;
        }
        
        @keyframes heroPulse {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 12px 40px rgba(0, 0, 0, 0.3), 0 0 60px rgba(139, 92, 246, 0.3);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 16px 50px rgba(0, 0, 0, 0.4), 0 0 80px rgba(139, 92, 246, 0.5);
            }
        }
        
        @keyframes rotateGlow {
            0% { filter: hue-rotate(0deg); }
            100% { filter: hue-rotate(360deg); }
        }
        
        @keyframes gradientRotate {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .hero-icon i {
            font-size: 50px;
            color: white;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
            animation: iconBounce 2s ease-in-out infinite;
            position: relative;
            z-index: 1;
        }
        
        @keyframes iconBounce {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            25% { transform: translateY(-5px) rotate(-5deg); }
            75% { transform: translateY(-5px) rotate(5deg); }
        }
        
        .hero-section h1 {
            font-size: 52px;
            font-weight: 800;
            margin-bottom: 15px;
            color: #ffffff;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.4), 0 0 40px rgba(139, 92, 246, 0.3);
            animation: fadeInUp 0.8s ease, textGlow 3s ease-in-out infinite;
            background: linear-gradient(135deg, #ffffff 0%, #e0e7ff 50%, #ffffff 100%);
            background-size: 200% 200%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -1px;
        }
        
        @keyframes textGlow {
            0%, 100% { 
                background-position: 0% 50%;
                filter: drop-shadow(0 0 10px rgba(139, 92, 246, 0.5));
            }
            50% { 
                background-position: 100% 50%;
                filter: drop-shadow(0 0 20px rgba(139, 92, 246, 0.8));
            }
        }
        
        .hero-section h2 {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 20px;
            color: rgba(255, 255, 255, 0.95);
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
            animation: fadeInUp 1s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }
        
        .hero-section h2::before,
        .hero-section h2::after {
            content: '⚖️';
            font-size: 20px;
            animation: spin 3s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .hero-section p {
            font-size: 17px;
            max-width: 700px;
            margin: 0 auto 30px;
            line-height: 1.7;
            color: #ffffff;
            text-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
            animation: fadeInUp 1.2s ease;
        }
        
        /* Trust badges - moved to hero section */
        .trust-badges {
            margin: 30px auto 40px;
            max-width: 1000px;
            animation: fadeInUp 1.4s ease;
        }
        
        .trust-badges-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            padding: 0 20px;
        }
        
        .trust-badge {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(15px) saturate(180%);
            border: 2px solid rgba(255, 255, 255, 0.35);
            border-radius: 22px;
            padding: 28px 24px;
            text-align: center;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .trust-badge::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: rotate 8s linear infinite;
        }
        
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .trust-badge:hover {
            transform: translateY(-8px) scale(1.05);
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.5);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.25);
        }
        
        .trust-badge-icon {
            font-size: 36px;
            margin-bottom: 14px;
            color: #ffffff;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
            animation: floatIcon 3s ease-in-out infinite;
            position: relative;
            z-index: 1;
        }
        
        @keyframes floatIcon {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }
        
        .trust-badge-number {
            font-size: 48px;
            font-weight: 800;
            margin-bottom: 10px;
            color: #ffffff;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.4);
            background: linear-gradient(135deg, #ffffff 0%, #e0e7ff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            position: relative;
            z-index: 1;
            line-height: 1;
        }
        
        .trust-badge-label {
            font-size: 15px;
            color: rgba(255, 255, 255, 0.95);
            font-weight: 600;
            text-shadow: 0 1px 4px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 1;
        }
        
        .cta-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
            animation: fadeInUp 1.6s ease;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 60px;
            padding: 0 20px;
        }
        
        .feature-card {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(20px) saturate(180%);
            border: 2px solid rgba(255, 255, 255, 0.4);
            border-radius: 20px;
            padding: 28px 24px;
            text-align: center;
            color: #ffffff;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .feature-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: cardRotate 10s linear infinite;
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .feature-card:hover::before {
            opacity: 1;
        }
        
        @keyframes cardRotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .feature-card:hover {
            transform: translateY(-8px) scale(1.02) rotate(1deg);
            background: rgba(255, 255, 255, 0.35);
            box-shadow: 0 12px 36px rgba(0, 0, 0, 0.3), 0 0 30px rgba(139, 92, 246, 0.2);
            border-color: rgba(255, 255, 255, 0.6);
        }
        
        .feature-card:nth-child(even):hover {
            transform: translateY(-8px) scale(1.02) rotate(-1deg);
        }
        
        .feature-card h3 {
            color: #ffffff;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        }
        
        .feature-card p {
            color: #ffffff;
            text-shadow: 0 1px 4px rgba(0, 0, 0, 0.3);
        }
        
        .feature-icon {
            width: 70px;
            height: 70px;
            margin: 0 auto 18px;
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid rgba(255, 255, 255, 0.4);
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }
        
        .feature-icon::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.3) 0%, transparent 100%);
            opacity: 0;
            transition: opacity 0.4s;
        }
        
        .feature-card:hover .feature-icon {
            transform: scale(1.1) rotate(5deg);
            background: rgba(255, 255, 255, 0.35);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        }
        
        .feature-card:hover .feature-icon::before {
            opacity: 1;
        }
        
        .feature-icon i {
            font-size: 32px;
            color: white;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 1;
            transition: all 0.4s ease;
        }
        
        .feature-card:hover .feature-icon i {
            transform: scale(1.2);
            filter: drop-shadow(0 0 10px rgba(255, 255, 255, 0.5));
        }
        
        .feature-card h3 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 12px;
        }
        
        .feature-card p {
            font-size: 14px;
            line-height: 1.6;
            opacity: 0.9;
        }
        
        .stats-section {
            margin-top: 80px;
            padding: 60px 20px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 30px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .stat-item {
            text-align: center;
            color: #ffffff;
        }
        
        .stat-number {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 10px;
            color: #ffffff;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.4);
        }
        
        .stat-label {
            font-size: 16px;
            color: #ffffff;
            font-weight: 500;
            text-shadow: 0 1px 4px rgba(0, 0, 0, 0.3);
        }
        
        /* Gen-Z style button */
        .btn-modern {
            position: relative;
            overflow: hidden;
            padding: 18px 40px;
            font-size: 18px;
            font-weight: 700;
            min-width: 200px;
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.4);
            border-radius: 50px;
            color: #ffffff;
            text-decoration: none;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }
        
        .btn-modern::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        .btn-modern:hover::before {
            width: 400px;
            height: 400px;
        }
        
        .btn-modern:hover {
            transform: translateY(-4px) scale(1.05);
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.6);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.3), 0 0 40px rgba(255, 255, 255, 0.2);
        }
        
        .btn-modern span,
        .btn-modern i {
            position: relative;
            z-index: 1;
        }
        
        .btn-modern i {
            transition: transform 0.3s;
        }
        
        .btn-modern:hover i {
            transform: translateX(5px) rotate(15deg);
        }
        
        /* Why Choose Us Section */
        .why-choose-section {
            margin-top: 80px;
            padding: 60px 20px;
            position: relative;
        }
        
        .why-choose-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .section-title {
            text-align: center;
            font-size: 42px;
            font-weight: 800;
            margin-bottom: 50px;
            color: #ffffff;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
            background: linear-gradient(135deg, #ffffff 0%, #e0e7ff 50%, #ffffff 100%);
            background-size: 200% 200%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: textGlow 3s ease-in-out infinite;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }
        
        .section-title i {
            font-size: 36px;
            color: #ffd700;
            text-shadow: 0 0 20px rgba(255, 215, 0, 0.6);
            animation: starTwinkle 2s ease-in-out infinite;
        }
        
        @keyframes starTwinkle {
            0%, 100% { 
                transform: scale(1) rotate(0deg);
                opacity: 1;
            }
            50% { 
                transform: scale(1.2) rotate(180deg);
                opacity: 0.8;
            }
        }
        
        .benefits-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .benefit-card {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(20px) saturate(180%);
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 20px;
            padding: 28px 24px;
            text-align: center;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .benefit-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: cardRotate 12s linear infinite;
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .benefit-card:hover::before {
            opacity: 1;
        }
        
        .benefit-card:hover {
            transform: translateY(-8px) scale(1.02);
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
            box-shadow: 0 12px 36px rgba(0, 0, 0, 0.3), 0 0 30px rgba(139, 92, 246, 0.3);
        }
        
        .benefit-icon-wrapper {
            width: 70px;
            height: 70px;
            margin: 0 auto 18px;
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid rgba(255, 255, 255, 0.4);
            transition: all 0.4s ease;
            position: relative;
            z-index: 1;
        }
        
        .benefit-card:hover .benefit-icon-wrapper {
            transform: scale(1.1) rotate(5deg);
            background: rgba(255, 255, 255, 0.35);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
        }
        
        .benefit-icon-wrapper i {
            font-size: 32px;
            color: white;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            transition: all 0.4s ease;
        }
        
        .benefit-card:hover .benefit-icon-wrapper i {
            transform: scale(1.2);
            filter: drop-shadow(0 0 15px rgba(255, 255, 255, 0.6));
        }
        
        .benefit-card h3 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 12px;
            color: #ffffff;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 1;
        }
        
        .benefit-card p {
            font-size: 14px;
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.9);
            text-shadow: 0 1px 4px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 1;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        @keyframes wave {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(20deg); }
            75% { transform: rotate(-20deg); }
        }
        
        @media (max-width: 768px) {
            .nav-content-wrapper {
                flex-direction: column;
                gap: 15px;
                padding: 0 20px;
            }
            
            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
                gap: 15px;
            }
            
            .hero-section h1 {
                font-size: 36px;
            }
            
            .hero-section h2 {
                font-size: 20px;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
            }
            
            .top-navbar {
                padding: 12px 0;
            }
        }
    </style>
</head>
<body class="welcome-page">
    <!-- Floating Particles -->
    <div class="floating-particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>
    
    <!-- Top Navigation Bar -->
    <nav class="top-navbar">
        <div class="nav-content-wrapper">
            <a href="index.php" class="nav-brand">
                <i class="fas fa-gavel"></i>
                <span>Law Firm System</span>
            </a>
            <ul class="nav-links">
                <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="#features"><i class="fas fa-star"></i> Features</a></li>
                <li><a href="client/login.php" class="btn-client-login"><i class="fas fa-user-circle"></i> Client Login</a></li>
                <li><a href="login.php" class="btn-signin"><i class="fas fa-sign-in-alt"></i> Staff Sign In</a></li>
            </ul>
        </div>
    </nav>
    
    <div class="welcome-container">
        <div class="hero-section">
            <div class="hero-icon">
                <i class="fas fa-gavel"></i>
            </div>
            <h1>Law Firm Management System</h1>
            <h2>Munyoki Maheli and Company Advocates</h2>
            <p>Streamline your law firm operations with our comprehensive management system. Manage cases, clients, advocates, appointments, and billing all in one place.</p>
            
            <!-- Trust Badges -->
            <div class="trust-badges">
                <div class="trust-badges-grid">
                    <div class="trust-badge">
                        <div class="trust-badge-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="trust-badge-number">3</div>
                        <div class="trust-badge-label">User Roles</div>
                    </div>
                    <div class="trust-badge">
                        <div class="trust-badge-icon">
                            <i class="fas fa-cube"></i>
                        </div>
                        <div class="trust-badge-number">9</div>
                        <div class="trust-badge-label">Core Modules</div>
                    </div>
                    <div class="trust-badge">
                        <div class="trust-badge-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="trust-badge-number">100%</div>
                        <div class="trust-badge-label">Secure & Reliable</div>
                    </div>
                    <div class="trust-badge">
                        <div class="trust-badge-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="trust-badge-number">24/7</div>
                        <div class="trust-badge-label">Access Available</div>
                    </div>
                </div>
            </div>
            
            <div class="cta-buttons">
                <a href="#features" class="btn-modern">
                    <span>Explore Features</span>
                    <i class="fas fa-arrow-down"></i>
                </a>
            </div>
        </div>
        
        <div id="features" class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3>Client Management</h3>
                <p>Efficiently manage client information, contact details, and case history. Keep all client data organized and easily accessible.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-folder-open"></i>
                </div>
                <h3>Case Tracking</h3>
                <p>Track all legal cases with detailed information including case type, court, status, and assigned advocates. Never lose track of important cases.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-briefcase"></i>
                </div>
                <h3>Advocate Management</h3>
                <p>Manage advocate profiles and assign cases to the right legal professionals. Track case assignments and workload distribution.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <h3>Event Scheduling</h3>
                <p>Schedule and manage court hearings, meetings, consultations, and other important events. Never miss an important appointment.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <h3>Billing & Payments</h3>
                <p>Track billing information, deposits, installments, and payment status. Keep financial records organized and up-to-date.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <h3>Reports & Analytics</h3>
                <p>Generate comprehensive reports on cases, billing, and system statistics. Make data-driven decisions for your law firm.</p>
            </div>
        </div>
        
        <!-- Why Choose Us Section -->
        <div class="why-choose-section">
            <div class="why-choose-container">
                <h2 class="section-title">
                    <i class="fas fa-star"></i>
                    Why Choose Our System?
                    <i class="fas fa-star"></i>
                </h2>
                <div class="benefits-grid">
                    <div class="benefit-card">
                        <div class="benefit-icon-wrapper">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <h3>Lightning Fast</h3>
                        <p>Experience blazing-fast performance with optimized database queries and efficient workflows.</p>
                    </div>
                    <div class="benefit-card">
                        <div class="benefit-icon-wrapper">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h3>Fully Responsive</h3>
                        <p>Access your law firm management system from any device, anywhere, anytime.</p>
                    </div>
                    <div class="benefit-card">
                        <div class="benefit-icon-wrapper">
                            <i class="fas fa-lock"></i>
                        </div>
                        <h3>Bank-Level Security</h3>
                        <p>Your sensitive legal data is protected with industry-standard encryption and security measures.</p>
                    </div>
                    <div class="benefit-card">
                        <div class="benefit-icon-wrapper">
                            <i class="fas fa-sync-alt"></i>
                        </div>
                        <h3>Real-Time Updates</h3>
                        <p>Stay synchronized with real-time case updates, notifications, and collaboration tools.</p>
                    </div>
                    <div class="benefit-card">
                        <div class="benefit-icon-wrapper">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3>Data Analytics</h3>
                        <p>Make informed decisions with comprehensive reports and visual analytics dashboards.</p>
                    </div>
                    <div class="benefit-card">
                        <div class="benefit-icon-wrapper">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h3>24/7 Support</h3>
                        <p>Round-the-clock assistance ensures your operations never skip a beat.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer style="background: rgba(15, 23, 42, 0.9); color: #ffffff; padding: 40px 20px; text-align: center; margin-top: 80px;">
        <div style="max-width: 1400px; margin: 0 auto;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 40px; margin-bottom: 30px; text-align: left;">
                <div>
                    <h4 style="color: #a78bfa; margin-bottom: 15px; font-size: 18px;"><i class="fas fa-gavel"></i> Law Firm System</h4>
                    <p style="color: #cbd5e1; line-height: 1.8; font-size: 14px;">Comprehensive management system for modern law firms. Streamline operations and improve efficiency.</p>
                </div>
                <div>
                    <h4 style="color: #a78bfa; margin-bottom: 15px; font-size: 18px;">Quick Links</h4>
                    <ul style="list-style: none; padding: 0;">
                        <li style="margin-bottom: 10px;"><a href="index.php" style="color: #cbd5e1; text-decoration: none; transition: color 0.3s;"><i class="fas fa-home"></i> Home</a></li>
                        <li style="margin-bottom: 10px;"><a href="login.php" style="color: #cbd5e1; text-decoration: none; transition: color 0.3s;"><i class="fas fa-sign-in-alt"></i> Sign In</a></li>
                    </ul>
                    <style>
                        footer a:hover {
                            color: #a78bfa !important;
                        }
                    </style>
                </div>
                <div>
                    <h4 style="color: #a78bfa; margin-bottom: 15px; font-size: 18px;">Contact</h4>
                    <p style="color: #cbd5e1; line-height: 1.8; font-size: 14px;">
                        <i class="fas fa-building"></i> Munyoki Maheli and Company Advocates<br>
                        <i class="fas fa-envelope"></i> info@lawfirm.com<br>
                        <i class="fas fa-phone"></i> +254 700 000 000
                    </p>
                </div>
            </div>
            <div style="border-top: 1px solid rgba(255, 255, 255, 0.1); padding-top: 20px; color: #94a3b8; font-size: 14px;">
                <p>&copy; <?php echo date('Y'); ?> Law Firm Management System. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>
