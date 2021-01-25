import { Team } from '@apps/fyziklani/helpers/interfaces';

export class Filter {
    public name: string;
    public category: string;
    public roomId: number;

    constructor({roomId, category, name}) {
        this.category = category;
        this.roomId = roomId;
        this.name = name;
    }

    public match(team: Team): boolean {
        const {category} = team;
        return !(this.category && this.category !== category);

    }

    public same(filter: Filter): boolean {
        if (!filter) {
            return false;
        }
        return (filter.roomId === this.roomId) && (filter.category === this.category) && (filter.name === this.name);
    }

    public getHeadline(): string {
        return this.name;
    }
}
