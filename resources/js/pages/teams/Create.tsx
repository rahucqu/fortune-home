import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Sparkles, Users } from 'lucide-react';
import { FormEventHandler } from 'react';

export default function Create() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('teams.store'));
    };

    return (
        <AppLayout>
            <Head title="Create Team" />

            <div className="m-10">
                <div className="flex items-center justify-between">
                    <h1 className="text-3xl font-bold tracking-tight">Create New Team</h1>

                    <div className="flex items-center space-x-2">
                        <Link href={route('teams.members')}>
                            <Button variant="outline" size="sm">
                                <Users className="mr-2 h-4 w-4" />
                                View Members
                            </Button>
                        </Link>
                        <Link href={route('dashboard')}>
                            <Button variant="outline" size="sm">
                                <ArrowLeft className="mr-2 h-4 w-4" />
                                Back to Dashboard
                            </Button>
                        </Link>
                    </div>
                </div>

                {/* Team Creation Benefits */}
                <Card className="my-5 border-dashed">
                    <CardContent className="pt-6">
                        <div className="flex items-center space-x-3">
                            <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10">
                                <Sparkles className="h-5 w-5 text-primary" />
                            </div>
                            <div>
                                <h3 className="font-semibold">Why create a team?</h3>
                                <p className="text-sm text-muted-foreground">
                                    Organize projects, invite collaborators, and manage permissions all in one place.
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Team Creation Form */}
                <Card className="my-5">
                    <CardHeader>
                        <CardTitle className="flex items-center space-x-2">
                            <Users className="h-5 w-5" />
                            <span>Team Details</span>
                        </CardTitle>
                        <CardDescription>Choose a name for your team. You can always change this later in team settings.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={submit} className="space-y-6">
                            <div className="space-y-2">
                                <Label htmlFor="name">Team Name</Label>
                                <Input
                                    id="name"
                                    type="text"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    placeholder="e.g., Marketing Team, Product Development, Sales Team"
                                    className="w-full"
                                    autoFocus
                                />
                                {errors.name && <p className="text-sm text-red-600 dark:text-red-400">{errors.name}</p>}
                                <p className="text-xs text-muted-foreground">Choose a descriptive name that your team members will recognize.</p>
                            </div>

                            <div className="flex items-center justify-end space-x-2 pt-4">
                                <Link href={route('dashboard')}>
                                    <Button variant="outline" type="button">
                                        Cancel
                                    </Button>
                                </Link>
                                <Button type="submit" disabled={processing || !data.name.trim()}>
                                    {processing ? 'Creating Team...' : 'Create Team'}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>

                {/* Next Steps Preview */}
                <Card className="my-5 bg-muted/50">
                    <CardContent className="pt-6">
                        <h3 className="mb-3 font-semibold">What happens next?</h3>
                        <ul className="space-y-2 text-sm text-muted-foreground">
                            <li className="flex items-center space-x-2">
                                <div className="h-1.5 w-1.5 rounded-full bg-primary"></div>
                                <span>Your team will be created and you'll be automatically switched to it</span>
                            </li>
                            <li className="flex items-center space-x-2">
                                <div className="h-1.5 w-1.5 rounded-full bg-primary"></div>
                                <span>You can invite team members via email from the team dashboard</span>
                            </li>
                            <li className="flex items-center space-x-2">
                                <div className="h-1.5 w-1.5 rounded-full bg-primary"></div>
                                <span>Manage roles and permissions for each team member</span>
                            </li>
                        </ul>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
