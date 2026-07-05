<?php
declare(strict_types=1);

class UserSyncHandler {
    public function handleSync() {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (!$data || !isset($data['email'])) {
            Http::response(400, 'Invalid data');
        }

        // Verify API Key (simplified)
        if ($_SERVER['HTTP_X_API_KEY'] !== $this->getApiKey()) {
            Http::response(403, 'Forbidden');
        }

        $user = User::lookupByEmail($data['email']);
        if (!$user) {
            // Create new user
            $user = User::fromVars([
                'email' => $data['email'],
                'name' => $data['firstname'] . ' ' . $data['lastname'],
            ]);
            $user->save();
        } else {
            // Update existing user
            $user->set('name', $data['firstname'] . ' ' . $data['lastname']);
            $user->save();
        }

        // Sync password hash if using a custom auth backend in osTicket
        // This requires an osTicket plugin for authentication that checks these hashes.

        Http::response(200, 'OK');
    }

    private function getApiKey() {
        // Retrieve from plugin config
        return 'your_api_key_here';
    }
}
