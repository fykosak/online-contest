export interface Task {
    number: string;
    name: string;
    groupId: number;
    taskId: number;
}

export interface Submit {
    points: number | null;
    skipped: boolean;
    taskId: number;
    teamId: number;
}

export interface Team {
    teamId: number;
    category: string;
    name: string;
    status: string;
}
