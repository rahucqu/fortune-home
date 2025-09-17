import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import AdminLayout from '@/layouts/Admin/admin-layout';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Save } from 'lucide-react';

interface CreateAgentData {
    name: string;
    email: string;
    phone: string;
    bio: string;
    license_number: string;
    specializations: string;
    years_experience: number;
    languages_spoken: string;
    avatar_url: string;
    social_facebook: string;
    social_twitter: string;
    social_linkedin: string;
    social_instagram: string;
    office_address: string;
    office_phone: string;
    is_active: boolean;
}

export default function AgentsCreate() {
    const { data, setData, post, processing, errors } = useForm<CreateAgentData>({
        name: '',
        email: '',
        phone: '',
        bio: '',
        license_number: '',
        specializations: '',
        years_experience: 0,
        languages_spoken: '',
        avatar_url: '',
        social_facebook: '',
        social_twitter: '',
        social_linkedin: '',
        social_instagram: '',
        office_address: '',
        office_phone: '',
        is_active: true,
    });

    const breadcrumbs = [
        { title: 'Admin', href: '/admin' },
        { title: 'Agents', href: '/admin/agents' },
        { title: 'Create Agent', href: '/admin/agents/create' },
    ];

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/admin/agents');
    };

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Agent" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Create Agent</h1>
                        <p className="text-muted-foreground">
                            Add a new real estate agent to your team
                        </p>
                    </div>
                    <Button variant="outline" asChild>
                        <Link href="/admin/agents">
                            <ArrowLeft className="h-4 w-4 mr-2" />
                            Back to Agents
                        </Link>
                    </Button>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Basic Information</CardTitle>
                            <CardDescription>
                                Enter the basic details for the new agent.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-6">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div className="space-y-2">
                                    <Label htmlFor="name">Full Name</Label>
                                    <Input
                                        id="name"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        placeholder="Agent's full name"
                                        required
                                    />
                                    {errors.name && (
                                        <p className="text-sm text-destructive">{errors.name}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="email">Email</Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        value={data.email}
                                        onChange={(e) => setData('email', e.target.value)}
                                        placeholder="agent@example.com"
                                        required
                                    />
                                    {errors.email && (
                                        <p className="text-sm text-destructive">{errors.email}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="phone">Phone</Label>
                                    <Input
                                        id="phone"
                                        value={data.phone}
                                        onChange={(e) => setData('phone', e.target.value)}
                                        placeholder="+1 (555) 123-4567"
                                    />
                                    {errors.phone && (
                                        <p className="text-sm text-destructive">{errors.phone}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="license_number">License Number</Label>
                                    <Input
                                        id="license_number"
                                        value={data.license_number}
                                        onChange={(e) => setData('license_number', e.target.value)}
                                        placeholder="Real estate license number"
                                    />
                                    {errors.license_number && (
                                        <p className="text-sm text-destructive">{errors.license_number}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="years_experience">Years of Experience</Label>
                                    <Input
                                        id="years_experience"
                                        type="number"
                                        value={data.years_experience}
                                        onChange={(e) => setData('years_experience', parseInt(e.target.value) || 0)}
                                        placeholder="0"
                                        min="0"
                                    />
                                    {errors.years_experience && (
                                        <p className="text-sm text-destructive">{errors.years_experience}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="languages_spoken">Languages Spoken</Label>
                                    <Input
                                        id="languages_spoken"
                                        value={data.languages_spoken}
                                        onChange={(e) => setData('languages_spoken', e.target.value)}
                                        placeholder="English, Spanish, French"
                                    />
                                    {errors.languages_spoken && (
                                        <p className="text-sm text-destructive">{errors.languages_spoken}</p>
                                    )}
                                </div>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="bio">Bio</Label>
                                <Textarea
                                    id="bio"
                                    value={data.bio}
                                    onChange={(e) => setData('bio', e.target.value)}
                                    placeholder="Agent's professional biography..."
                                    rows={4}
                                />
                                {errors.bio && (
                                    <p className="text-sm text-destructive">{errors.bio}</p>
                                )}
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="specializations">Specializations</Label>
                                <Input
                                    id="specializations"
                                    value={data.specializations}
                                    onChange={(e) => setData('specializations', e.target.value)}
                                    placeholder="Luxury homes, First-time buyers, Commercial"
                                />
                                {errors.specializations && (
                                    <p className="text-sm text-destructive">{errors.specializations}</p>
                                )}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Contact & Office Information</CardTitle>
                            <CardDescription>
                                Office and contact details for the agent.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-6">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div className="space-y-2">
                                    <Label htmlFor="office_phone">Office Phone</Label>
                                    <Input
                                        id="office_phone"
                                        value={data.office_phone}
                                        onChange={(e) => setData('office_phone', e.target.value)}
                                        placeholder="+1 (555) 987-6543"
                                    />
                                    {errors.office_phone && (
                                        <p className="text-sm text-destructive">{errors.office_phone}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="avatar_url">Avatar URL</Label>
                                    <Input
                                        id="avatar_url"
                                        value={data.avatar_url}
                                        onChange={(e) => setData('avatar_url', e.target.value)}
                                        placeholder="https://example.com/avatar.jpg"
                                    />
                                    {errors.avatar_url && (
                                        <p className="text-sm text-destructive">{errors.avatar_url}</p>
                                    )}
                                </div>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="office_address">Office Address</Label>
                                <Textarea
                                    id="office_address"
                                    value={data.office_address}
                                    onChange={(e) => setData('office_address', e.target.value)}
                                    placeholder="Office address..."
                                    rows={2}
                                />
                                {errors.office_address && (
                                    <p className="text-sm text-destructive">{errors.office_address}</p>
                                )}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Social Media</CardTitle>
                            <CardDescription>
                                Social media profiles for the agent.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-6">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div className="space-y-2">
                                    <Label htmlFor="social_facebook">Facebook</Label>
                                    <Input
                                        id="social_facebook"
                                        value={data.social_facebook}
                                        onChange={(e) => setData('social_facebook', e.target.value)}
                                        placeholder="https://facebook.com/username"
                                    />
                                    {errors.social_facebook && (
                                        <p className="text-sm text-destructive">{errors.social_facebook}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="social_twitter">Twitter</Label>
                                    <Input
                                        id="social_twitter"
                                        value={data.social_twitter}
                                        onChange={(e) => setData('social_twitter', e.target.value)}
                                        placeholder="https://twitter.com/username"
                                    />
                                    {errors.social_twitter && (
                                        <p className="text-sm text-destructive">{errors.social_twitter}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="social_linkedin">LinkedIn</Label>
                                    <Input
                                        id="social_linkedin"
                                        value={data.social_linkedin}
                                        onChange={(e) => setData('social_linkedin', e.target.value)}
                                        placeholder="https://linkedin.com/in/username"
                                    />
                                    {errors.social_linkedin && (
                                        <p className="text-sm text-destructive">{errors.social_linkedin}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="social_instagram">Instagram</Label>
                                    <Input
                                        id="social_instagram"
                                        value={data.social_instagram}
                                        onChange={(e) => setData('social_instagram', e.target.value)}
                                        placeholder="https://instagram.com/username"
                                    />
                                    {errors.social_instagram && (
                                        <p className="text-sm text-destructive">{errors.social_instagram}</p>
                                    )}
                                </div>
                            </div>

                            <div className="flex items-center space-x-2">
                                <Switch
                                    id="is_active"
                                    checked={data.is_active}
                                    onCheckedChange={(checked) => setData('is_active', checked)}
                                />
                                <Label htmlFor="is_active">Active</Label>
                            </div>
                        </CardContent>
                    </Card>

                    <div className="flex items-center space-x-2">
                        <Button type="submit" disabled={processing}>
                            <Save className="h-4 w-4 mr-2" />
                            {processing ? 'Creating...' : 'Create Agent'}
                        </Button>
                        <Button type="button" variant="outline" asChild>
                            <Link href="/admin/agents">Cancel</Link>
                        </Button>
                    </div>
                </form>
            </div>
        </AdminLayout>
    );
}
