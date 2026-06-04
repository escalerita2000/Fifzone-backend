<?php
try {
    $pdo = new PDO('pgsql:host=aws-1-us-west-2.pooler.supabase.com;port=5432;dbname=postgres;sslmode=require', 'postgres.itxwtnusxwpifliupwdp', '5kHGL5hbTKLjdWXB');
    
    echo "=== Triggers on auth.users ===\n";
    $stmt = $pdo->query("SELECT trigger_name, event_manipulation, action_statement FROM information_schema.triggers WHERE event_object_schema = 'auth' AND event_object_table = 'users'");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo $e->getMessage();
}
