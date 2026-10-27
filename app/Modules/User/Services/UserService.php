<?php

namespace App\Modules\User\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Enums\UserStatus;
use App\Modules\User\DTO\UserData;
use App\Models\UserPermissionOverride;

class UserService
{
    public function create(UserData $dto): User
    {
        return DB::transaction(function () use ($dto) {

            $user = User::create([
                'name' => $dto->name,
                'email' => $dto->email,
                'status' => UserStatus::ACTIVE,
                'password' => Hash::make($dto->password),
                
            ]);

            if (!empty($dto->roles)) {
                $user->syncRoles($dto->roles);
            }
            
            if ($dto->autoVerify) {
            $user->markEmailAsVerified();
        }


            return $user;
        });
    }

    
public function update(User $user, UserData $dto): User
{
    $oldStatus = $user->status;

    $oldRoleIds = $user->roles()->pluck('id')->toArray();

    return DB::transaction(function () use ($user, $dto, $oldStatus, $oldRoleIds) {

        $user->update([
            'name' => $dto->name,
            'email' => $dto->email,
            'status' => $dto->status,
            'password' => $dto->password
                ? Hash::make($dto->password)
                : $user->password,
        ]);

        // sync roles
        $user->syncRoles($dto->roles);

        $newRoleIds = $dto->roles;

        /*
        |-----------------------------------------
        | ROLE CHANGE DETECTED
        |-----------------------------------------
        */
        $roleChanged = array_diff($oldRoleIds, $newRoleIds)
            || array_diff($newRoleIds, $oldRoleIds);

        /*
        |-----------------------------------------
        | ALWAYS CLEAR OLD OVERRIDES
        |-----------------------------------------
        */
        if ($roleChanged || isset($dto->overrides)) {
            UserPermissionOverride::where('model_id', $user->id)
                ->where('model_type', User::class)
                ->delete();
        }

        /*
        |-----------------------------------------
        | APPLY NEW OVERRIDES (IF PROVIDED)
        |-----------------------------------------
        */
        if (!empty($dto->overrides)) {
            foreach ($dto->overrides as $override) {
                UserPermissionOverride::create([
                    'model_id' => $user->id,
                    'model_type' => User::class,
                    'permission_id' => $override['permission_id'],
                    'effect' => $override['effect'],
                ]);
            }
        }

        /*
        |-----------------------------------------
        | STATUS LOGIC
        |-----------------------------------------
        */
        if (in_array($dto->status, [
            UserStatus::DEACTIVATED,
            UserStatus::SUSPENDED,
        ], true)) {
            $user->email_verified_at = null;
            $user->save();
        } elseif (
            $oldStatus !== UserStatus::ACTIVE &&
            $dto->status === UserStatus::ACTIVE
        ) {
            $user->markEmailAsVerified();
        }

        return $user;
    });
}

    public function delete(User $user): void
    {
        DB::transaction(function () use ($user) {
            $user->delete();
        });
    }
}