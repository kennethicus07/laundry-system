/** AUTO-GENERATED FILE. DO NOT EDIT. */

export const PermissionStatus = {
  ACTIVE: 1,
  INACTIVE: 0,
} as const;

export type PermissionStatusType = typeof PermissionStatus[keyof typeof PermissionStatus];

export const RoleStatus = {
  ACTIVE: 1,
  INACTIVE: 0,
} as const;

export type RoleStatusType = typeof RoleStatus[keyof typeof RoleStatus];

export const Status = {
  ACTIVE: 1,
  INACTIVE: 0,
} as const;

export type StatusType = typeof Status[keyof typeof Status];

export const UserStatus = {
  DEACTIVATED: 0,
  ACTIVE: 1,
  PENDING: 2,
  SUSPENDED: 5,
} as const;

export type UserStatusType = typeof UserStatus[keyof typeof UserStatus];

