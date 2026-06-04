<?php
try {
    $pdo = new PDO('pgsql:host=aws-1-us-west-2.pooler.supabase.com;port=5432;dbname=postgres;sslmode=require', 'postgres.itxwtnusxwpifliupwdp', '5kHGL5hbTKLjdWXB');
    
    echo "=== Columns in auth.users ===\n";
    $stmt = $pdo->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'users' AND table_schema = 'auth'");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo $e->getMessage();
}
